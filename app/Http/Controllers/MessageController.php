<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Сообщения
 *
 * API для работы с личными сообщениями между пользователями
 */
class MessageController extends Controller
{

    public function index()
    {
        $response = [
            'data' => [
                [
                    'id' => 1,
                    'conversation_id' => 123,
                    'preview' => 'Привет! Как дела?',
                    'read' => false,
                    'created_at' => '2023-10-01T14:30:00Z',
                    'from' => [
                        'username' => 'user123',
                        'avatar' => [
                            'path' => 'https://example.com/avatars/user123.jpg'
                        ]
                    ]
                ],
                [
                    'id' => 2,
                    'conversation_id' => 123,
                    'preview' => 'Всё отлично, спасибо!',
                    'read' => true,
                    'created_at' => '2023-10-01T14:35:00Z',
                    'from' => [
                        'username' => 'user456',
                        'avatar' => [
                            'path' => 'https://example.com/avatars/user456.jpg'
                        ]
                    ]
                ],
                [
                    'id' => 3,
                    'conversation_id' => 456,
                    'preview' => 'Напоминаю про встречу завтра.',
                    'read' => false,
                    'created_at' => '2023-10-02T09:00:00Z',
                    'from' => [
                        'username' => 'user789',
                        'avatar' => null
                    ]
                ]
            ]
        ];

        return $response;
    }

    /**
     * Получение списка чатов
     *
     * Возвращает список чатов (диалогов) текущего пользователя
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @response {
     *  "success": true,
     *  "data": [
     *    {
     *      "id": 1,
     *      "user": {
     *        "id": 2,
     *        "username": "janedoe",
     *        "verification": false,
     *        "avatar": {
     *          "path": "avatars/user2.png"
     *        },
     *        "online": true
     *      },
     *      "last_message": {
     *        "content": "Привет, как дела?",
     *        "created_at": "2023-03-30T10:15:00Z",
     *        "is_read": false,
     *        "is_mine": false
     *      },
     *      "unread_count": 3
     *    }
     *  ]
     * }
     */
    public function getChats()
    {
        // Здесь будет логика получения списка чатов
        // Возвращаем заглушку в правильном формате
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Получение сообщений чата
     *
     * Возвращает историю сообщений с конкретным пользователем
     *
     * @param Request $request
     * @param int $user_id ID пользователя (собеседника)
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam user_id required ID пользователя, с которым ведется переписка. Example: 2
     * @queryParam page integer Номер страницы для пагинации. Example: 1
     * @queryParam per_page integer Количество сообщений на странице. Example: 20
     *
     * @response {
     *  "success": true,
     *  "data": {
     *    "user": {
     *      "id": 2,
     *      "username": "janedoe",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/user2.png"
     *      },
     *      "online": true,
     *      "last_seen": "2023-03-30T15:45:00Z"
     *    },
     *    "messages": [
     *      {
     *        "id": 123,
     *        "sender_id": 2,
     *        "content": "Привет, как дела?",
     *        "is_read": true,
     *        "created_at": "2023-03-30T10:15:00Z"
     *      },
     *      {
     *        "id": 124,
     *        "sender_id": 1,
     *        "content": "Все отлично, спасибо! А у тебя?",
     *        "is_read": true,
     *        "created_at": "2023-03-30T10:17:00Z"
     *      }
     *    ]
     *  },
     *  "pagination": {
     *    "total": 30,
     *    "per_page": 20,
     *    "current_page": 1,
     *    "last_page": 2
     *  }
     * }
     */
    public function getMessages(Request $request, $user_id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        // Здесь будет логика получения сообщений
        // Возвращаем заглушку в правильном формате
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (int)$user_id,
                    'username' => 'user_' . $user_id,
                    'verification' => false,
                    'avatar' => [
                        'path' => 'avatars/default.png'
                    ],
                    'online' => false,
                    'last_seen' => now()->toISOString()
                ],
                'messages' => []
            ],
            'pagination' => [
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => 1
            ]
        ]);
    }

    /**
     * Отправка сообщения
     *
     * Отправка нового сообщения пользователю
     *
     * @param Request $request
     * @param int $user_id ID пользователя (получателя)
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam user_id required ID пользователя, которому отправляется сообщение. Example: 2
     * @bodyParam content string required Текст сообщения. Example: Привет! Как твои дела?
     *
     * @response 201 {
     *  "success": true,
     *  "data": {
     *    "id": 125,
     *    "sender_id": 1,
     *    "recipient_id": 2,
     *    "content": "Привет! Как твои дела?",
     *    "is_read": false,
     *    "created_at": "2023-03-30T16:00:00Z"
     *  },
     *  "message": "Сообщение успешно отправлено"
     * }
     *
     * @response 422 {
     *  "success": false,
     *  "message": "Ошибка валидации",
     *  "errors": {
     *    "content": ["Текст сообщения не может быть пустым."]
     *  }
     * }
     */
    public function sendMessage(Request $request, $user_id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        // Здесь будет логика отправки сообщения
        // Возвращаем заглушку в правильном формате
        return response()->json([
            'success' => true,
            'data' => [
                'id' => rand(1, 1000),
                'sender_id' => auth()->id() ?? 1,
                'recipient_id' => (int)$user_id,
                'content' => $request->input('content'),
                'is_read' => false,
                'created_at' => now()->toISOString()
            ],
            'message' => 'Сообщение успешно отправлено'
        ], 201);
    }

    /**
     * Отметить сообщения как прочитанные
     *
     * Пометить все непрочитанные сообщения от указанного пользователя как прочитанные
     *
     * @param int $user_id ID пользователя (отправителя)
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @urlParam user_id required ID пользователя, сообщения которого нужно отметить прочитанными. Example: 2
     *
     * @response {
     *  "success": true,
     *  "message": "Сообщения отмечены как прочитанные"
     * }
     */
    public function markAsRead($user_id)
    {
        // Здесь будет логика отметки сообщений как прочитанных
        return response()->json([
            'success' => true,
            'message' => 'Сообщения отмечены как прочитанные'
        ]);
    }
}
