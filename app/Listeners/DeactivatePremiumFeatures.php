<?php

namespace App\Listeners;

use App\Events\SubscriptionCancelled;
use App\Models\Users\UserPremiumFeature;
use Illuminate\Support\Facades\Log;

class DeactivatePremiumFeatures
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
    public function handle(SubscriptionCancelled $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->user;

        Log::info('ДеактивацияПремиумФункций: Деактивация Premium функций', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
        ]);

        // Получаем запись premium_features для пользователя
        $premiumFeatures = UserPremiumFeature::where('user_id', $user->id)->first();

        if ($premiumFeatures) {
            // Деактивируем Premium функции
            $premiumFeatures->deactivatePremium();

            Log::info('ДеактивацияПремиумФункций: Premium функции деактивированы', [
                'user_id' => $user->id,
                'premium_features' => $premiumFeatures->toArray(),
            ]);
        } else {
            Log::warning('ДеактивацияПремиумФункций: Premium функции не найдены', [
                'user_id' => $user->id,
            ]);
        }
    }
}
