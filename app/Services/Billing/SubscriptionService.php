<?php

namespace App\Services\Billing;

use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Models\Billing\Subscription;
use Illuminate\Support\Facades\Auth;

class SubscriptionService
{
    // Получить текущую активную подписку пользователя
    public function getActiveSubscription(): Subscription
    {
        return Subscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }

    // Создание новой подписки
    public function createSubscription($plan, $amount, $currency, $duration)
    {
        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan' => $plan,
            'status' => 'active',
            'amount' => $amount,
            'currency' => $currency,
            'started_at' => now(),
            'expires_at' => now()->add($duration),
        ]);

        // Диспатчим событие активации подписки
        event(new SubscriptionActivated($subscription));

        return $subscription;
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
