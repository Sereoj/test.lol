<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Комментарии
 * @description Управление комментариями в системе
 */
class CommentController extends Controller
{
    /**
     * Получение списка комментариев
     * 
     * Возвращает пагинированный список комментариев с возможностью фильтрации и сортировки.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * @queryParam search string Поиск по содержимому комментария. Example: отличный
     * @queryParam sort string Поле для сортировки (id, created_at). Example: created_at
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     * @queryParam status string Фильтр по статусу (approved, pending, spam). Example: approved
     * @queryParam user_id integer Фильтр по ID автора. Example: 2
     * @queryParam post_id integer Фильтр по ID поста. Example: 3
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "content": "Отличный пост! Очень полезная информация.",
     *             "status": "approved",
     *             "author": {
     *                 "id": 3,
     *                 "name": "Александр Николаев"
     *             },
     *             "post": {
     *                 "id": 1,
     *                 "title": "Первый пост"
     *             },
     *             "created_at": "2025-03-15T14:20:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "content": "Спасибо за интересную статью!",
     *             "status": "approved",
     *             "author": {
     *                 "id": 4,
     *                 "name": "Екатерина Смирнова"
     *             },
     *             "post": {
     *                 "id": 1,
     *                 "title": "Первый пост"
     *             },
     *             "created_at": "2025-03-15T15:30:00.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 5,
     *         "path": "http://example.com/api/admin/comments",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 75
     *     }
     * }
     */
    public function index(Request $request)
    {
        // Здесь должен быть код для получения списка комментариев
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'content' => 'Отличный пост! Очень полезная информация.',
                    'status' => 'approved',
                    'author' => [
                        'id' => 3,
                        'name' => 'Александр Николаев'
                    ],
                    'post' => [
                        'id' => 1,
                        'title' => 'Первый пост'
                    ],
                    'created_at' => '2025-03-15T14:20:00.000000Z'
                ],
                [
                    'id' => 2,
                    'content' => 'Спасибо за интересную статью!',
                    'status' => 'approved',
                    'author' => [
                        'id' => 4,
                        'name' => 'Екатерина Смирнова'
                    ],
                    'post' => [
                        'id' => 1,
                        'title' => 'Первый пост'
                    ],
                    'created_at' => '2025-03-15T15:30:00.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 5,
                'path' => 'http://example.com/api/admin/comments',
                'per_page' => 15,
                'to' => 15,
                'total' => 75
            ]
        ]);
    }

    /**
     * Получение данных комментария
     * 
     * Возвращает подробную информацию о комментарии.
     *
     * @urlParam id integer required ID комментария. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "content": "Отличный пост! Очень полезная информация.",
     *         "status": "approved",
     *         "author": {
     *             "id": 3,
     *             "name": "Александр Николаев",
     *             "email": "alex@example.com",
     *             "avatar": "https://example.com/avatars/alex.jpg"
     *         },
     *         "post": {
     *             "id": 1,
     *             "title": "Первый пост",
     *             "url": "https://example.com/posts/1"
     *         },
     *         "parent_id": null,
     *         "likes_count": 5,
     *         "ip_address": "192.168.1.1",
     *         "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
     *         "created_at": "2025-03-15T14:20:00.000000Z",
     *         "updated_at": "2025-03-15T14:20:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="комментарий не найден" {
     *     "success": false,
     *     "message": "Комментарий не найден"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения данных комментария
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'content' => 'Отличный пост! Очень полезная информация.',
                'status' => 'approved',
                'author' => [
                    'id' => 3,
                    'name' => 'Александр Николаев',
                    'email' => 'alex@example.com',
                    'avatar' => 'https://example.com/avatars/alex.jpg'
                ],
                'post' => [
                    'id' => 1,
                    'title' => 'Первый пост',
                    'url' => 'https://example.com/posts/1'
                ],
                'parent_id' => null,
                'likes_count' => 5,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => '2025-03-15T14:20:00.000000Z',
                'updated_at' => '2025-03-15T14:20:00.000000Z'
            ]
        ]);
    }

    /**
     * Обновление комментария
     * 
     * Обновляет данные существующего комментария.
     *
     * @urlParam id integer required ID комментария. Example: 1
     * 
     * @bodyParam content string Содержание комментария. Example: Обновленный текст комментария.
     * @bodyParam status string Статус комментария (approved, pending, spam). Example: approved
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное обновление" {
     *     "success": true,
     *     "message": "Комментарий успешно обновлен",
     *     "data": {
     *         "id": 1,
     *         "content": "Обновленный текст комментария.",
     *         "status": "approved",
     *         "updated_at": "2025-03-30T15:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="комментарий не найден" {
     *     "success": false,
     *     "message": "Комментарий не найден"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "content": ["Комментарий не может быть пустым"]
     *     }
     * }
     */
    public function update(Request $request, $id)
    {
        // Здесь должен быть код для обновления комментария
        return response()->json([
            'success' => true,
            'message' => 'Комментарий успешно обновлен',
            'data' => [
                'id' => 1,
                'content' => 'Обновленный текст комментария.',
                'status' => 'approved',
                'updated_at' => '2025-03-30T15:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Удаление комментария
     * 
     * Удаляет комментарий из системы.
     *
     * @urlParam id integer required ID комментария. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное удаление" {
     *     "success": true,
     *     "message": "Комментарий успешно удален"
     * }
     * 
     * @response status=404 scenario="комментарий не найден" {
     *     "success": false,
     *     "message": "Комментарий не найден"
     * }
     */
    public function destroy($id)
    {
        // Здесь должен быть код для удаления комментария
        return response()->json([
            'success' => true,
            'message' => 'Комментарий успешно удален'
        ]);
    }

    /**
     * Одобрение комментария
     * 
     * Одобряет комментарий для публикации.
     *
     * @urlParam id integer required ID комментария. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное одобрение" {
     *     "success": true,
     *     "message": "Комментарий одобрен",
     *     "data": {
     *         "id": 1,
     *         "status": "approved"
     *     }
     * }
     * 
     * @response status=404 scenario="комментарий не найден" {
     *     "success": false,
     *     "message": "Комментарий не найден"
     * }
     * 
     * @response status=400 scenario="комментарий уже одобрен" {
     *     "success": false,
     *     "message": "Комментарий уже одобрен"
     * }
     */
    public function approve($id)
    {
        // Здесь должен быть код для одобрения комментария
        return response()->json([
            'success' => true,
            'message' => 'Комментарий одобрен',
            'data' => [
                'id' => 1,
                'status' => 'approved'
            ]
        ]);
    }

    /**
     * Отклонение комментария
     * 
     * Отклоняет комментарий и помечает его как спам.
     *
     * @urlParam id integer required ID комментария. Example: 1
     * 
     * @bodyParam reason string Причина отклонения комментария. Example: Спам-контент
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное отклонение" {
     *     "success": true,
     *     "message": "Комментарий отклонен",
     *     "data": {
     *         "id": 1,
     *         "status": "spam",
     *         "reject_reason": "Спам-контент"
     *     }
     * }
     * 
     * @response status=404 scenario="комментарий не найден" {
     *     "success": false,
     *     "message": "Комментарий не найден"
     * }
     */
    public function reject(Request $request, $id)
    {
        // Здесь должен быть код для отклонения комментария
        return response()->json([
            'success' => true,
            'message' => 'Комментарий отклонен',
            'data' => [
                'id' => 1,
                'status' => 'spam',
                'reject_reason' => 'Спам-контент'
            ]
        ]);
    }

    /**
     * Получение комментариев на модерации
     * 
     * Возвращает пагинированный список комментариев, ожидающих модерации.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 3,
     *             "content": "Это комментарий на модерации",
     *             "status": "pending",
     *             "author": {
     *                 "id": 5,
     *                 "name": "Дмитрий Кузнецов"
     *             },
     *             "post": {
     *                 "id": 2,
     *                 "title": "Второй пост"
     *             },
     *             "created_at": "2025-03-30T10:15:00.000000Z"
     *         },
     *         {
     *             "id": 4,
     *             "content": "Еще один комментарий на модерации",
     *             "status": "pending",
     *             "author": {
     *                 "id": 6,
     *                 "name": "Ольга Ильина"
     *             },
     *             "post": {
     *                 "id": 1,
     *                 "title": "Первый пост"
     *             },
     *             "created_at": "2025-03-30T11:20:00.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 2,
     *         "path": "http://example.com/api/admin/comments/pending",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 18
     *     }
     * }
     */
    public function pending(Request $request)
    {
        // Здесь должен быть код для получения комментариев на модерации
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 3,
                    'content' => 'Это комментарий на модерации',
                    'status' => 'pending',
                    'author' => [
                        'id' => 5,
                        'name' => 'Дмитрий Кузнецов'
                    ],
                    'post' => [
                        'id' => 2,
                        'title' => 'Второй пост'
                    ],
                    'created_at' => '2025-03-30T10:15:00.000000Z'
                ],
                [
                    'id' => 4,
                    'content' => 'Еще один комментарий на модерации',
                    'status' => 'pending',
                    'author' => [
                        'id' => 6,
                        'name' => 'Ольга Ильина'
                    ],
                    'post' => [
                        'id' => 1,
                        'title' => 'Первый пост'
                    ],
                    'created_at' => '2025-03-30T11:20:00.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 2,
                'path' => 'http://example.com/api/admin/comments/pending',
                'per_page' => 15,
                'to' => 15,
                'total' => 18
            ]
        ]);
    }

    /**
     * Получение спам-комментариев
     * 
     * Возвращает пагинированный список комментариев, отмеченных как спам.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 5,
     *             "content": "Это спам-комментарий",
     *             "status": "spam",
     *             "author": {
     *                 "id": 7,
     *                 "name": "Спам Бот"
     *             },
     *             "post": {
     *                 "id": 3,
     *                 "title": "Третий пост"
     *             },
     *             "created_at": "2025-03-29T18:45:00.000000Z",
     *             "reject_reason": "Автоматическая фильтрация спама"
     *         },
     *         {
     *             "id": 6,
     *             "content": "Еще один спам-комментарий",
     *             "status": "spam",
     *             "author": {
     *                 "id": 8,
     *                 "name": "Другой Спам Бот"
     *             },
     *             "post": {
     *                 "id": 2,
     *                 "title": "Второй пост"
     *             },
     *             "created_at": "2025-03-29T19:10:00.000000Z",
     *             "reject_reason": "Содержит запрещенные ссылки"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 1,
     *         "path": "http://example.com/api/admin/comments/spam",
     *         "per_page": 15,
     *         "to": 6,
     *         "total": 6
     *     }
     * }
     */
    public function spam(Request $request)
    {
        // Здесь должен быть код для получения спам-комментариев
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 5,
                    'content' => 'Это спам-комментарий',
                    'status' => 'spam',
                    'author' => [
                        'id' => 7,
                        'name' => 'Спам Бот'
                    ],
                    'post' => [
                        'id' => 3,
                        'title' => 'Третий пост'
                    ],
                    'created_at' => '2025-03-29T18:45:00.000000Z',
                    'reject_reason' => 'Автоматическая фильтрация спама'
                ],
                [
                    'id' => 6,
                    'content' => 'Еще один спам-комментарий',
                    'status' => 'spam',
                    'author' => [
                        'id' => 8,
                        'name' => 'Другой Спам Бот'
                    ],
                    'post' => [
                        'id' => 2,
                        'title' => 'Второй пост'
                    ],
                    'created_at' => '2025-03-29T19:10:00.000000Z',
                    'reject_reason' => 'Содержит запрещенные ссылки'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'path' => 'http://example.com/api/admin/comments/spam',
                'per_page' => 15,
                'to' => 6,
                'total' => 6
            ]
        ]);
    }
} 