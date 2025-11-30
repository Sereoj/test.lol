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

// Контроллер для работы с настройками уведомлений пользователя
class UserNotificationSettingsController extends Controller
{
    protected UserNotificationSettingsService $notificationSettingsService;

    public function __construct(UserNotificationSettingsService $notificationSettingsService)
    {
        $this->notificationSettingsService = $notificationSettingsService;
    }

    // Получение настроек уведомлений пользователя   
    
    /**
     * @OA\Patch(
     *     path="/api/v1/user/notifications",
     *     tags={"Users"},
     *     summary="Update user notification settings",
     *     description="Update user notification settings",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateNotificationSettingsRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
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
