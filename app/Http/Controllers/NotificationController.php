<?php

namespace App\Http\Controllers;

use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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

    }

    /**
     * Получить список всех уведомлений пользователя.
     */
    public function index(): JsonResponse
    {
        try {
            return $this->successResponse([]);
/*            $notifications = $this->notificationService->getAllForUser(auth()->id());
            $unreadCount = $this->notificationService->getUnreadCount(auth()->id());

            return $this->successResponse(
                'Notifications retrieved successfully',
                [
                    'notifications' => new NotificationCollection($notifications),
                    'unread_count' => $unreadCount
                ]
            );*/
        } catch (\Exception $e) {
            Log::error('Error retrieving notifications: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error retrieving notifications');
        }
    }

    /**
     * Получить список непрочитанных уведомлений пользователя.
     */
    public function unread(): JsonResponse
    {
        try {
            return $this->successResponse([]);
/*            $notifications = $this->notificationService->getUnreadForUser(auth()->id());
            $unreadCount = $this->notificationService->getUnreadCount(auth()->id());

            return $this->successResponse(
                'Unread notifications retrieved successfully',
                [
                    'notifications' => new NotificationCollection($notifications),
                    'unread_count' => $unreadCount
                ]
            );*/
        } catch (\Exception $e) {
            Log::error('Error retrieving unread notifications: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error retrieving unread notifications');
        }
    }

    /**
     * Отметить конкретное уведомление как прочитанное.
     *
     * @param string $id
     */
    public function markAsRead(string $id): JsonResponse
    {
        try {
            return $this->successResponse([]);
/*            $notification = $this->notificationService->markAsRead($id, auth()->id());

            if (!$notification) {
                return $this->errorResponse('Notification not found', 404);
            }

            event(new NotificationRead($notification));

            return $this->successResponse(
                'Notification marked as read successfully',
                new NotificationResource($notification)
            );*/
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage(), [
                'notification_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error marking notification as read');
        }
    }

    /**
     * Отметить все уведомления пользователя как прочитанные.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            return $this->successResponse([]);
/*            $notifications = $this->notificationService->markAllAsRead(auth()->id());

            foreach ($notifications as $notification) {
                event(new NotificationRead($notification));
            }

            return $this->successResponse('All notifications marked as read successfully');*/
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error marking all notifications as read');
        }
    }

    /**
     * Удаление уведомления
     *
     * Удаляет конкретное уведомление пользователя.
     *
     * @param string $id
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
    public function delete(string $id): JsonResponse
    {
        try {
            return $this->successResponse([]);
/*            $notification = $this->notificationService->getById($id);

            if (!$notification || $notification->user_id !== auth()->id()) {
                return $this->errorResponse('Notification not found', 404);
            }

            $result = $this->notificationService->delete($id, auth()->id());

            if ($result) {
                event(new NotificationDeleted($notification));
                return $this->successResponse('Notification deleted successfully');
            }

            return $this->errorResponse('Error deleting notification');*/
        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage(), [
                'notification_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error deleting notification');
        }
    }
}
