<?php

namespace App\Listeners;

use App\Events\SubscriptionActivated;
use App\Models\Users\UserPremiumFeature;
use Illuminate\Support\Facades\Log;

class ActivatePremiumFeatures
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->user;

        Log::info('ActivatePremiumFeatures: Activating premium features', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
        ]);

        // Получаем или создаем запись premium_features для пользователя
        $premiumFeatures = UserPremiumFeature::firstOrCreate(
            ['user_id' => $user->id],
            [
                'has_no_ads' => false,
                'has_premium_badge' => false,
                'upload_limit' => 20,
                'max_file_size' => 50,
            ]
        );

        // Активируем Premium функции
        $premiumFeatures->activatePremium();

        Log::info('ActivatePremiumFeatures: Premium features activated', [
            'user_id' => $user->id,
            'premium_features' => $premiumFeatures->toArray(),
        ]);
    }
}
