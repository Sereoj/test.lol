<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Получение списка уведомлений
     *
     * Возвращает список уведомлений текущего пользователя с пагинацией.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @queryParam page integer Номер страницы для пагинации. Example: 1
     * @queryParam per_page integer Количество элементов на странице. Example: 15
     * @queryParam is_read boolean Фильтр по статусу прочтения (true - прочитанные, false - непрочитанные). Example: false
     *
     * @response {
     *  "success": true,
     *  "data": [
     *    {
     *      "id": "1234abcd-5678-efgh-ijkl-9012mnop3456",
     *      "type": "new_follower",
     *      "data": {
     *        "user": {
     *          "id": 3,
     *          "username": "alexsmith",
     *          "verification": true,
     *          "avatar": {
     *            "path": "avatars/user3.png"
     *          }
     *        }
     *      },
     *      "is_read": false,
     *      "created_at": "2023-03-30T14:25:00Z"
     *    },
     *    {
     *      "id": "7890abcd-1234-efgh-ijkl-5678mnop9012",
     *      "type": "post_like",
     *      "data": {
     *        "user": {
     *          "id": 2,
     *          "username": "janedoe",
     *          "verification": false,
     *          "avatar": {
     *            "path": "avatars/user2.png"
     *          }
     *        },
     *        "post": {
     *          "id": 15,
     *          "content": "Это мой новый пост!"
     *        }
     *      },
     *      "is_read": false,
     *      "created_at": "2023-03-29T18:35:00Z"
     *    }
     *  ],
     *  "pagination": {
     *    "total": 24,
     *    "per_page": 15,
     *    "current_page": 1,
     *    "last_page": 2
     *  }
     * }
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);

        try {
            $notification = $this->notificationService->allNotification($user, $page, $perPage);
            return $this->successResponse($notification);
        } catch (Exception $exception)
        {
            Log::error('Ошибка при получении уведомлений: ' . $exception->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $exception->getTraceAsString()
            ]);
            return $this->errorResponse('Не удалось получить уведомления', 500);
        }

  /*      // Здесь будет логика получения уведомлений
        // Возвращаем заглушку в правильном формате
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'type' => 'like',
                    'message' => 'Пользователь user123 лайкнул ваш пост.',
                    'read' => false,
                    'created_at' => '2023-10-01T14:30:00Z',
                    'data' => [
                        'post_id' => 123,
                        'comment_id' => null
                    ],
                    'from' => [
                        'username' => 'user123',
                        'avatar' => [
                            'path' => 'https://example.com/avatars/user123.jpg'
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'type' => 'comment',
                    'message' => 'Пользователь user456 прокомментировал ваш пост.',
                    'read' => true,
                    'created_at' => '2023-10-01T14:35:00Z',
                    'data' => [
                        'post_id' => 123,
                        'comment_id' => 456
                    ],
                    'from' => [
                        'username' => 'user456',
                        'avatar' => [
                            'path' => 'https://example.com/avatars/user456.jpg'
                        ]
                    ]
                ],
                [
                    'id' => 3,
                    'type' => 'follow',
                    'message' => 'Пользователь user789 начал следить за вами.',
                    'read' => false,
                    'created_at' => '2023-10-02T09:00:00Z',
                    'data' => [
                        'post_id' => null,
                        'comment_id' => null
                    ],
                    'from' => [
                        'username' => 'user789',
                        'avatar' => null
                    ]
                ]
            ],
            'pagination' => [
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => 1
            ]
        ]);*/
    }

    /**
     * Получение количества непрочитанных уведомлений
     *
     * Возвращает количество непрочитанных уведомлений текущего пользователя.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @response {
     *  "success": true,
     *  "data": {
     *    "count": 5
     *  }
     * }
     */
    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            $notification = $this->notificationService->getUnreadCount($user);
            return $this->successResponse($notification);
        }catch (Exception $exception)
        {
            Log::error('Ошибка получения непрочитанных сообщений', [
                'user_id' => Auth::id(),
                'exception' => $exception->getTraceAsString()
            ]);
            return $this->errorResponse('Ошибка получения непрочитанных сообщений', 500);
        }
    }

    /**
     * Отметить уведомление как прочитанное
     *
     * Помечает отдельное уведомление как прочитанное.
     *
     * @param string $id ID уведомления
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam id required ID уведомления. Example: 1234abcd-5678-efgh-ijkl-9012mnop3456
     *
     * @response {
     *  "success": true,
     *  "message": "Уведомление отмечено как прочитанное"
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Уведомление не найдено"
     * }
     */
    public function markAsRead($id)
    {
        // Здесь будет логика отметки уведомления как прочитанного
        return response()->json([
            'success' => true,
            'message' => 'Уведомление отмечено как прочитанное'
        ]);
    }

    /**
     * Отметить все уведомления как прочитанные
     *
     * Помечает все непрочитанные уведомления пользователя как прочитанные.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @response {
     *  "success": true,
     *  "message": "Все уведомления отмечены как прочитанные"
     * }
     */
    public function markAllAsRead()
    {
        // Здесь будет логика отметки всех уведомлений как прочитанных
        return response()->json([
            'success' => true,
            'message' => 'Все уведомления отмечены как прочитанные'
        ]);
    }

    /**
     * Удаление уведомления
     *
     * Удаляет конкретное уведомление пользователя.
     *
     * @param string $id ID уведомления
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam id required ID уведомления. Example: 1234abcd-5678-efgh-ijkl-9012mnop3456
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
    public function destroy($id)
    {
        // В реальном приложении здесь будет логика удаления уведомления
        return response()->json([
            'success' => true,
            'message' => 'Уведомление удалено'
        ]);
    }
}
