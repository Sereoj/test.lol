<?php

namespace App\Listeners;

use App\Events\UserActivity;
use App\Events\UserWentOnline;
use App\Models\Users\UserOnlineStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateOnlineStatus implements ShouldQueue
{
    public function handle(UserActivity $event)
    {
        $onlineStatus = UserOnlineStatus::updateOrCreate(
            ['user_id' => $event->user->id],
            [
                'last_activity' => now(),
                'device_type' => $event->deviceType,
                'ip_address' => $event->ipAddress,
            ]
        );

        if ($onlineStatus->wasRecentlyCreated || $onlineStatus->wasChanged('last_activity')) {
            broadcast(new UserWentOnline($event->user));
        }

        Log::info("User {$event->user->id} is active.");
    }
}
