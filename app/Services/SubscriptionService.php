<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class SubscriptionService
{
    // Получить текущую активную подписку пользователя
    public function getActiveSubscription()
    {
        return Subscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }

    // Создание новой подписки
    public function createSubscription($plan, $amount, $currency, $duration)
    {
        return Subscription::create([
            'user_id' => Auth::id(),
            'plan' => $plan,
            'status' => 'active',
            'amount' => $amount,
            'currency' => $currency,
            'started_at' => now(),
            'expires_at' => now()->add($duration),
        ]);
    }

    // Обновить статус подписки
    public function updateSubscriptionStatus($subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription) {
            $subscription->updateStatus();
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

    public function checkAndUpdateSubscriptionStatus()
    {
        $subscription = $this->getActiveSubscription();
        if ($subscription) {
            $this->updateSubscriptionStatus($subscription->id);
        }
    }
}
