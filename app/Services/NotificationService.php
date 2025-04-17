<?php

namespace App\Services;

use App\Models\Users\User;

class NotificationService
{
    public function allNotification(User $user, $page, $perPage)
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['read' => true]);
        return $notification;
    }

    public function getUnreadCount(User $user)
    {

    }
}
