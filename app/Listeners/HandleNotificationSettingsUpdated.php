<?php

namespace App\Listeners;

use App\Events\NotificationSettingsUpdated;
use Illuminate\Support\Facades\Log;

class HandleNotificationSettingsUpdated
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
    public function handle(NotificationSettingsUpdated $event): void
    {
        Log::info('Настройки уведомлений обновлены для пользователя', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
        ]);
    }
}
