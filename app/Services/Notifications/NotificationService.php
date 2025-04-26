<?php

namespace App\Services\Notifications;

use App\Models\Notifications\Notification;
use App\Models\Users\User;

class NotificationService
{
    public function getAllForUser(User $user)
    {
        return Notification::where('user_id', $user->id)->get();
    }

    public function getUnreadCount(User $user)
    {
    }
}
