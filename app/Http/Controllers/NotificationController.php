<?php

namespace App\Http\Controllers;

use App\Events\NotificationSent;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $this->notificationService = $notificationService;
    }

    /**
     * Получить список всех уведомлений пользователя.
     */
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();
            $notifications = $this->notificationService->getAllForUser($user);
            $unreadCount = $this->notificationService->getUnreadCount($user);

            return $this->successResponse(
                'Notifications retrieved successfully',
                [
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount
                ]
            );
        } catch (\Exception $e) {
            Log::error('Ошибка при получении уведомлений: ' . $e->getMessage(), [
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
            $user = auth()->user();
            $notifications = $this->notificationService->getUnreadForUser($user);
            $unreadCount = $this->notificationService->getUnreadCount($user);

            return $this->successResponse(
                'Unread notifications retrieved successfully',
                [
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount
                ]
            );
        } catch (\Exception $e) {
            Log::error('Ошибка при получении непрочитанных уведомлений: ' . $e->getMessage(), [
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
            $notification = $this->notificationService->markAsRead($id, auth()->id());

            if (!$notification) {
                return $this->errorResponse('Notification not found', 404);
            }

            return $this->successResponse(
                'Notification marked as read successfully',
                ['notification' => $notification]
            );
        } catch (\Exception $e) {
            Log::error('Ошибка при отметке уведомления как прочитанного: ' . $e->getMessage(), [
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
            $userId = auth()->id();
            $this->notificationService->markAllAsRead($userId);

            return $this->successResponse('All notifications marked as read successfully');
        } catch (\Exception $e) {
            Log::error('Ошибка при отметке всех уведомлений как прочитанных: ' . $e->getMessage(), [
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
            $notification = $this->notificationService->getById($id);

            // Check if notification exists and belongs to the authenticated user
            if (!$notification || $notification->notifiable_id != auth()->id()) {
                return $this->errorResponse('Notification not found', 404);
            }

            $result = $this->notificationService->delete($id, auth()->id());

            if ($result) {
                return $this->successResponse('Notification deleted successfully');
            }

            return $this->errorResponse('Error deleting notification');
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении уведомления: ' . $e->getMessage(), [
                'notification_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Error deleting notification');
        }
    }

    /**
     * Отправить уведомление пользователю через WebSocket
     *
     * Отправляет уведомление конкретному пользователю в реальном времени через WebSocket.
     *
     * @param Request $request
     *
     * @authenticated
     *
     * @bodyParam user_id integer required ID пользователя-получателя. Example: 1
     * @bodyParam type string required Тип уведомления. Example: new_follower
     * @bodyParam title string required Заголовок уведомления. Example: Новый подписчик
     * @bodyParam message string required Текст уведомления. Example: У вас новый подписчик
     * @bodyParam data array Дополнительные данные уведомления. Example: {"follower_id": 2}
     *
     * @response {
     *  "success": true,
     *  "message": "Notification sent successfully"
     * }
     *
     * @response 422 {
     *  "success": false,
     *  "message": "Validation error",
     *  "errors": {
     *      "user_id": ["The user_id field is required."]
     *  }
     * }
     */
    public function send(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'type' => 'required|string',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'data' => 'nullable|array',
            ]);

            $notification = [
                'id' => uniqid(),
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'data' => $validated['data'] ?? [],
                'created_at' => now()->toIso8601String(),
            ];

            // Отправка уведомления через WebSocket
            broadcast(new NotificationSent($validated['user_id'], $notification));

            return $this->successResponse('Notification sent successfully', [
                'notification' => $notification
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation error', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке уведомления: ' . $e->getMessage(), [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Error sending notification');
        }
    }
}
