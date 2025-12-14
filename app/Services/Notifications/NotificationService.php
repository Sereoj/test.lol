<?php

namespace App\Services\Notifications;

use App\Models\Notifications\Notification;
use App\Models\Users\User;

class NotificationService
{
    public function getAllForUser(User $user)
    {
        return $user->notifications()->orderBy('created_at', 'desc')->get();
    }

    public function getUnreadCount(User $user)
    {
        return $user->notifications()->whereNull('read_at')->count();
    }

    public function getUnreadForUser(User $user)
    {
        return $user->notifications()->whereNull('read_at')->orderBy('created_at', 'desc')->get();
    }

    public function markAsRead($id, $userId)
    {
        $notification = $this->getById($id);
        if ($notification && $notification->notifiable_id == $userId) {
            $notification->read_at = now();
            $notification->save();
            return $notification;
        }
        return null;
    }

    public function markAllAsRead($userId)
    {
        return tap(
            $this->getAllForUser($this->getUserById($userId))->whereNull('read_at'),
            function ($notifications) {
                $notifications->each->markAsRead();
            }
        );
    }

    public function getById($id)
    {
        return Notification::find($id);
    }

    public function delete($id, $userId)
    {
        $notification = $this->getById($id);
        if ($notification && $notification->notifiable_id == $userId) {
            return $notification->delete();
        }
        return false;
    }

    private function getUserById($id)
    {
        return \App\Models\Users\User::find($id);
    }
}
