<?php

namespace App\Http\Controllers\Users;

use App\Events\NotificationSettingsUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateNotificationSettingsRequest;
use App\Http\Resources\User\UserNotificationSettingsResource;
use App\Services\Users\UserNotificationSettingsService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserNotificationSettingsController extends Controller
{
    protected UserNotificationSettingsService $notificationSettingsService;

    public function __construct(UserNotificationSettingsService $notificationSettingsService)
    {
        $this->notificationSettingsService = $notificationSettingsService;
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $settings = $this->notificationSettingsService->get($user);
            return $this->successResponse(new UserNotificationSettingsResource($settings));
        }catch (Exception $e) {
            Log::error('Ошибка при получении настроек уведомлений: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Не удалось получить настройки уведомлений', 500);
        }
    }

    public function update(UpdateNotificationSettingsRequest $request)
    {
        try {
            $user = Auth::user();
            $settings = $this->notificationSettingsService->updateSettings($user, $request->validated());
            event(new NotificationSettingsUpdated($user));
            return $this->successResponse(new UserNotificationSettingsResource($settings));
        }catch (Exception $e) {
            Log::error('Ошибка при обновлении настроек уведомлений: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Не удалось обновить настройки уведомлений', 500);
        }
    }
}
