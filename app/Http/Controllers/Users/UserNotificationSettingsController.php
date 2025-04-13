<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserNotificationSettingsController extends Controller
{
    /**
     * Обновление настроек уведомлений пользователя
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notify_on_new_message' => 'sometimes|boolean',
            'notify_on_new_follower' => 'sometimes|boolean',
            'notify_on_post_like' => 'sometimes|boolean',
            'notify_on_comment' => 'sometimes|boolean',
            'notify_on_comment_like' => 'sometimes|boolean',
            'notify_on_mention' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Получение или создание настроек уведомлений
        $settings = $user->notificationSettings ?? $user->notificationSettings()->create([
            'notify_on_new_message' => true,
            'notify_on_new_follower' => true,
            'notify_on_post_like' => true,
            'notify_on_comment' => true,
            'notify_on_comment_like' => true,
            'notify_on_mention' => true
        ]);

        // Обновление настроек
        foreach ([
            'notify_on_new_message', 'notify_on_new_follower', 'notify_on_post_like',
            'notify_on_comment', 'notify_on_comment_like', 'notify_on_mention'
        ] as $setting) {
            if ($request->has($setting)) {
                $settings->$setting = $request->$setting;
            }
        }

        $settings->save();

        return response()->json([
            'success' => true,
            'data' => [
                'notify_on_new_message' => (bool)$settings->notify_on_new_message,
                'notify_on_new_follower' => (bool)$settings->notify_on_new_follower,
                'notify_on_post_like' => (bool)$settings->notify_on_post_like,
                'notify_on_comment' => (bool)$settings->notify_on_comment,
                'notify_on_comment_like' => (bool)$settings->notify_on_comment_like,
                'notify_on_mention' => (bool)$settings->notify_on_mention
            ],
            'message' => 'Настройки уведомлений успешно обновлены'
        ]);
    }
}
