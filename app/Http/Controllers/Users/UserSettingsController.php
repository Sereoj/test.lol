<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Users\UserSetting;

class UserSettingsController extends Controller
{
    /**
     * Обновление настроек пользователя
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_private' => 'sometimes|boolean',
            'show_online_status' => 'sometimes|boolean',
            'enable_two_factor' => 'sometimes|boolean',
            'is_preferences_feed' => 'sometimes|boolean',
            'preferences_feed' => 'sometimes|in:popularity,downloads,likes,default'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Получение или создание настроек пользователя
        $settings = $user->userSettings ?? new UserSetting();
        
        // Обновление настроек
        foreach ([
            'is_private', 'show_online_status', 'enable_two_factor',
            'is_preferences_feed', 'preferences_feed'
        ] as $setting) {
            if ($request->has($setting)) {
                $settings->$setting = $request->$setting;
            }
        }

        $settings->save();
        
        // Привязываем настройки к пользователю, если еще не привязаны
        if (!$user->userSettings_id) {
            $user->userSettings()->associate($settings);
            $user->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'is_private' => (bool)$settings->is_private,
                'show_online_status' => (bool)$settings->show_online_status,
                'enable_two_factor' => (bool)$settings->enable_two_factor,
                'is_preferences_feed' => (bool)$settings->is_preferences_feed,
                'preferences_feed' => $settings->preferences_feed
            ],
            'message' => 'Настройки пользователя успешно обновлены'
        ]);
    }
} 