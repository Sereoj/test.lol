<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Жалобы на пользователей
 * @description Управление жалобами на пользователей
 */
class UserReportController extends Controller
{
    /**
     * Получение списка жалоб на пользователей
     * 
     * Возвращает список жалоб на пользователей с возможностью фильтрации и пагинации.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * @queryParam status string Статус жалобы (pending, resolved, dismissed). Example: pending
     * @queryParam sort string Поле для сортировки (id, created_at, updated_at). Example: created_at
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     * @queryParam reported_user_id integer ID пользователя, на которого поступила жалоба. Example: 10
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "status": "pending",
     *             "reason": "spam",
     *             "description": "Пользователь отправляет спам-сообщения",
     *             "reported_user": {
     *                 "id": 10,
     *                 "name": "Спам-аккаунт",
     *                 "email": "spam@example.com"
     *             },
     *             "reporter": {
     *                 "id": 5,
     *                 "name": "Иван Петров"
     *             },
     *             "created_at": "2025-03-30T10:00:00.000000Z",
     *             "updated_at": "2025-03-30T10:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "status": "pending",
     *             "reason": "inappropriate_behavior",
     *             "description": "Пользователь оскорбляет других участников",
     *             "reported_user": {
     *                 "id": 15,
     *                 "name": "Проблемный пользователь",
     *                 "email": "problem@example.com"
     *             },
     *             "reporter": {
     *                 "id": 8,
     *                 "name": "Мария Сидорова"
     *             },
     *             "created_at": "2025-03-29T14:30:00.000000Z",
     *             "updated_at": "2025-03-29T14:30:00.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 2,
     *         "path": "http://example.com/api/admin/reports/users",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 25
     *     }
     * }
     */
    public function index(Request $request)
    {
        // Здесь должен быть код для получения списка жалоб на пользователей
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'status' => 'pending',
                    'reason' => 'spam',
                    'description' => 'Пользователь отправляет спам-сообщения',
                    'reported_user' => [
                        'id' => 10,
                        'name' => 'Спам-аккаунт',
                        'email' => 'spam@example.com'
                    ],
                    'reporter' => [
                        'id' => 5,
                        'name' => 'Иван Петров'
                    ],
                    'created_at' => '2025-03-30T10:00:00.000000Z',
                    'updated_at' => '2025-03-30T10:00:00.000000Z'
                ],
                [
                    'id' => 2,
                    'status' => 'pending',
                    'reason' => 'inappropriate_behavior',
                    'description' => 'Пользователь оскорбляет других участников',
                    'reported_user' => [
                        'id' => 15,
                        'name' => 'Проблемный пользователь',
                        'email' => 'problem@example.com'
                    ],
                    'reporter' => [
                        'id' => 8,
                        'name' => 'Мария Сидорова'
                    ],
                    'created_at' => '2025-03-29T14:30:00.000000Z',
                    'updated_at' => '2025-03-29T14:30:00.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 2,
                'path' => 'http://example.com/api/admin/reports/users',
                'per_page' => 15,
                'to' => 15,
                'total' => 25
            ]
        ]);
    }

    /**
     * Получение детальной информации о жалобе
     * 
     * Возвращает подробную информацию о жалобе на пользователя.
     *
     * @urlParam id integer required ID жалобы. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "status": "pending",
     *         "reason": "spam",
     *         "description": "Пользователь отправляет спам-сообщения",
     *         "evidence": [
     *             {
     *                 "type": "message",
     *                 "content": "Текст спам-сообщения 1"
     *             },
     *             {
     *                 "type": "message",
     *                 "content": "Текст спам-сообщения 2"
     *             }
     *         ],
     *         "reported_user": {
     *             "id": 10,
     *             "name": "Спам-аккаунт",
     *             "email": "spam@example.com",
     *             "registered_at": "2025-01-15T08:30:00.000000Z",
     *             "status": "active",
     *             "previous_reports_count": 3
     *         },
     *         "reporter": {
     *             "id": 5,
     *             "name": "Иван Петров",
     *             "email": "ivan@example.com",
     *             "registered_at": "2024-01-10T10:00:00.000000Z"
     *         },
     *         "admin_notes": [],
     *         "created_at": "2025-03-30T10:00:00.000000Z",
     *         "updated_at": "2025-03-30T10:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="жалоба не найдена" {
     *     "success": false,
     *     "message": "Жалоба не найдена"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения детальной информации о жалобе
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'status' => 'pending',
                'reason' => 'spam',
                'description' => 'Пользователь отправляет спам-сообщения',
                'evidence' => [
                    [
                        'type' => 'message',
                        'content' => 'Текст спам-сообщения 1'
                    ],
                    [
                        'type' => 'message',
                        'content' => 'Текст спам-сообщения 2'
                    ]
                ],
                'reported_user' => [
                    'id' => 10,
                    'name' => 'Спам-аккаунт',
                    'email' => 'spam@example.com',
                    'registered_at' => '2025-01-15T08:30:00.000000Z',
                    'status' => 'active',
                    'previous_reports_count' => 3
                ],
                'reporter' => [
                    'id' => 5,
                    'name' => 'Иван Петров',
                    'email' => 'ivan@example.com',
                    'registered_at' => '2024-01-10T10:00:00.000000Z'
                ],
                'admin_notes' => [],
                'created_at' => '2025-03-30T10:00:00.000000Z',
                'updated_at' => '2025-03-30T10:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Подтверждение жалобы
     * 
     * Подтверждает жалобу на пользователя и применяет меры.
     *
     * @urlParam id integer required ID жалобы. Example: 1
     * 
     * @bodyParam action string required Действие, которое нужно применить (ban, warn, restrict). Example: warn
     * @bodyParam duration integer Продолжительность действия в днях (для временных ограничений). Example: 7
     * @bodyParam admin_comment string Комментарий администратора. Example: Проверил жалобу, действительно нарушение правил
     * @bodyParam notify_reporter boolean Уведомить отправителя жалобы о ее рассмотрении. Example: true
     * @bodyParam notify_reported boolean Уведомить нарушителя о принятых мерах. Example: true
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная обработка" {
     *     "success": true,
     *     "message": "Жалоба обработана, к пользователю применены меры",
     *     "data": {
     *         "id": 1,
     *         "status": "resolved",
     *         "action_taken": "warn",
     *         "action_by": {
     *             "id": 1,
     *             "name": "Администратор"
     *         },
     *         "resolved_at": "2025-03-30T12:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="жалоба не найдена" {
     *     "success": false,
     *     "message": "Жалоба не найдена"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "action": ["Необходимо выбрать действие"]
     *     }
     * }
     */
    public function resolve(Request $request, $id)
    {
        // Здесь должен быть код для подтверждения жалобы
        return response()->json([
            'success' => true,
            'message' => 'Жалоба обработана, к пользователю применены меры',
            'data' => [
                'id' => 1,
                'status' => 'resolved',
                'action_taken' => 'warn',
                'action_by' => [
                    'id' => 1,
                    'name' => 'Администратор'
                ],
                'resolved_at' => '2025-03-30T12:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Отклонение жалобы
     * 
     * Отклоняет жалобу на пользователя.
     *
     * @urlParam id integer required ID жалобы. Example: 1
     * 
     * @bodyParam reason string required Причина отклонения жалобы. Example: Недостаточно доказательств
     * @bodyParam admin_comment string Комментарий администратора. Example: Проверил переписку, нарушений правил не обнаружено
     * @bodyParam notify_reporter boolean Уведомить отправителя жалобы о ее отклонении. Example: true
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная обработка" {
     *     "success": true,
     *     "message": "Жалоба отклонена",
     *     "data": {
     *         "id": 1,
     *         "status": "dismissed",
     *         "dismiss_reason": "Недостаточно доказательств",
     *         "dismissed_by": {
     *             "id": 1,
     *             "name": "Администратор"
     *         },
     *         "dismissed_at": "2025-03-30T12:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="жалоба не найдена" {
     *     "success": false,
     *     "message": "Жалоба не найдена"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "reason": ["Необходимо указать причину отклонения"]
     *     }
     * }
     */
    public function dismiss(Request $request, $id)
    {
        // Здесь должен быть код для отклонения жалобы
        return response()->json([
            'success' => true,
            'message' => 'Жалоба отклонена',
            'data' => [
                'id' => 1,
                'status' => 'dismissed',
                'dismiss_reason' => 'Недостаточно доказательств',
                'dismissed_by' => [
                    'id' => 1,
                    'name' => 'Администратор'
                ],
                'dismissed_at' => '2025-03-30T12:00:00.000000Z'
            ]
        ]);
    }
} 