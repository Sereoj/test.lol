<?php

namespace App\Services\Billing;

use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Models\Billing\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    // Получить текущую активную подписку пользователя
    // ПРИМЕР ПОЧЕМУ ТАК РЕШИЛИ: Проверяем expires_at > now, потому что
    // подписка со статусом 'active' но истёкшим expires_at не считается активной.
    // Без этой проверки пользователь мог бы видеть истёкшую подписку как активную.
    public function getActiveSubscription(): Subscription
    {
        return Subscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('expires_at', '>', now()) // Важно: проверяем, что подписка ещё не истекла
            ->first();
    }

    // Создание новой подписки
    public function createSubscription($plan, $amount, $currency, $duration, ?string $idempotencyKey = null)
    {
        $userId = Auth::id();

        return DB::transaction(function () use ($userId, $plan, $amount, $currency, $duration, $idempotencyKey) {
            // Проверяем idempotency key для защиты от дубликатов (ВНУТРИ транзакции)
            if ($idempotencyKey) {
                $existingSubscription = Subscription::where('user_id', $userId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();
                if ($existingSubscription) {
                    return $existingSubscription;
                }
            }

            // Проверяем, нет ли уже активной подписки (ВНУТРИ транзакции)
            $activeSubscription = Subscription::where('user_id', $userId)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if ($activeSubscription) {
                throw new \Exception('У пользователя уже есть активная подписка.');
            }

            // Проверяем и списываем баланс пользователя
            $userBalance = \App\Models\Users\UserBalance::where('user_id', $userId)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->first();

            if (!$userBalance) {
                throw new \Exception('Баланс пользователя не найден.');
            }

            if ($userBalance->balance < $amount) {
                throw new \Exception('Недостаточно средств для подписки.');
            }

            // Списываем средства
            $userBalance->balance -= $amount;
            $userBalance->save();

            // Создаем подписку
            $subscription = Subscription::create([
                'user_id' => $userId,
                'plan' => $plan,
                'status' => 'active',
                'amount' => $amount,
                'currency' => $currency,
                'started_at' => now(),
                'expires_at' => now()->add($duration),
                'idempotency_key' => $idempotencyKey,
            ]);

            // Создаем транзакцию
            \App\Models\Billing\Transaction::create([
                'user_id' => $userId,
                'type' => 'subscription',
                'amount' => -$amount,
                'currency' => $currency,
                'status' => 'completed',
                'metadata' => ['subscription_id' => $subscription->id],
            ]);

            return $subscription;
        });

        // Диспатчим событие ПОСЛЕ транзакции
        event(new SubscriptionActivated($subscription));
    }

    // Обновить статус подписки
    public function updateSubscriptionStatus($subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription) {
            $oldStatus = $subscription->status;
            $subscription->updateStatus();

            // Если подписка истекла (статус изменился на expired), диспатчим событие отмены
            if ($oldStatus === 'active' && $subscription->status === 'expired') {
                event(new SubscriptionCancelled($subscription));
            }
        }
    }

    // Продлить подписку
    public function extendSubscription($subscriptionId, $duration)
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription && $subscription->isActive()) {
            $subscription->extendSubscription($duration);
        }
    }

    public function checkAndUpdateSubscriptionStatus(): void
    {
        $subscription = $this->getActiveSubscription();
        if ($subscription) {
            $this->updateSubscriptionStatus($subscription->id);
        }
    }

    // Отменить подписку
    public function cancelSubscription($subscriptionId): void
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription && $subscription->status === 'active') {
            $subscription->status = 'cancelled';
            $subscription->save();

            // Диспатчим событие отмены подписки
            event(new SubscriptionCancelled($subscription));
        }
    }
}
