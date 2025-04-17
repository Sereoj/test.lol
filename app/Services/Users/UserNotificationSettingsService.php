<?php

namespace App\Services\Users;

use App\Models\Users\User;

class UserNotificationSettingsService
{
    public function get(User $user)
    {
        return $user->notificationSettings;

    }

    public function updateSettings(User $user, array $data)
    {
        // Получение или создание настроек уведомлений
        $settings = $user->notificationSettings()->firstOrNew();

        // Обновление настроек
        foreach ($data as $key => $value) {
            if (in_array($key, [
                'email_enabled',
                'push_enabled',
                'notify_on_new_message',
                'notify_on_new_follower',
                'notify_on_post_like',
                'notify_on_comment',
                'notify_on_comment_like',
                'notify_on_mention'
            ])) {
                $settings->$key = $value;
            }
        }
        $settings->save();
        return $settings;
    }
}
