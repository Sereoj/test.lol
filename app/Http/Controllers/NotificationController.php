<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserNotificationSettingsResource;
use App\Http\Resources\UserNotificationResource;
use App\Services\Messaging\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * @group Уведомления
 *
 * API для работы с уведомлениями пользователя
 */
class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth:api');
    }

    /**
     * Получить список всех уведомлений пользователя.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $notifications = $this->notificationService->getNotificationsByUserId($userId);
            return $this->successResponse(UserNotificationResource::collection($notifications));
        } catch (Exception $e) {
            Log::error('Ошибка при получении уведомлений: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось получить уведомления', 500);
        }
    }

    /**
     * Получить список непрочитанных уведомлений пользователя.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unread(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $notifications = $this->notificationService->getNotificationsByUserId($userId)
                ->whereNull('read_at');
            return $this->successResponse($notifications);
        } catch (Exception $e) {
            Log::error('Ошибка при получении непрочитанных уведомлений: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось получить непрочитанные уведомления', 500);
        }
    }

    /**
     * Отметить конкретное уведомление как прочитанное.
     *
     * @param int $notificationId
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsRead(int $notificationId, Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $notification = $this->notificationService->getById($notificationId);

            if (!$notification || $notification->notifiable_id !== $userId || $notification->notifiable_type !== \App\Models\Users\User::class) {
                return $this->errorResponse('Уведомление не найдено или доступ запрещен', 404);
            }

            $this->notificationService->markAsRead($notificationId);
            return $this->successResponse(['message' => 'Уведомление отмечено как прочитанное']);
        } catch (Exception $e) {
            Log::error('Ошибка при отметке уведомления как прочитанного: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'notification_id' => $notificationId,
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось отметить уведомление как прочитанное', 500);
        }
    }

    /**
     * Отметить все уведомления пользователя как прочитанные.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $notifications = $this->notificationService->getNotificationsByUserId($userId)
                ->whereNull('read_at');

            foreach ($notifications as $notification) {
                $this->notificationService->markAsRead($notification->id);
            }

            return $this->successResponse(['message' => 'Все уведомления отмечены как прочитанные']);
        } catch (Exception $e) {
            Log::error('Ошибка при отметке всех уведомлений как прочитанных: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось отметить все уведомления как прочитанные', 500);
        }
    }

    public function getUnreadCount(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $notifications = $this->notificationService->getNotificationsByUserId($userId)
                ->whereNull('read_at');
            $count = $notifications->count();
            return $this->successResponse(['count' => $count]);
        } catch (Exception $e) {
            Log::error('Ошибка при получении количества непрочитанных уведомлений: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Ошибка получения непрочитанных уведомлений', 500);
        }
    }

    /**
     * Удаление уведомления
     *
     * Удаляет конкретное уведомление пользователя.
     *
     * @param int $id ID уведомления
     * @return JsonResponse
     *
     * @authenticated
     *
     * @urlParam id required ID уведомления. Example: 123
     *
     * @response {
     *  "success": true,
     *  "message": "Уведомление удалено"
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Уведомление не найдено"
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $notification = $this->notificationService->getById($id);

            if (!$notification || $notification->notifiable_id !== $userId || $notification->notifiable_type !== \App\Models\Users\User::class) {
                return $this->errorResponse('Уведомление не найдено или доступ запрещен', 404);
            }

            $this->notificationService->deleteNotification($notification);
            return $this->successResponse(['message' => 'Уведомление удалено']);
        } catch (Exception $e) {
            Log::error('Ошибка при удалении уведомления: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'notification_id' => $id,
                'exception' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось удалить уведомление', 500);
        }
    }
}
