<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Роли
 * @description Управление ролями пользователей в системе
 */
class RoleController extends Controller
{
    /**
     * Получение списка ролей
     * 
     * Возвращает список всех ролей в системе.
     *
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "user",
     *             "display_name": "Пользователь",
     *             "description": "Обычный пользователь системы с базовыми правами",
     *             "created_at": "2025-01-01T00:00:00.000000Z",
     *             "updated_at": "2025-01-01T00:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "name": "moderator",
     *             "display_name": "Модератор",
     *             "description": "Пользователь с правами модерации контента",
     *             "created_at": "2025-01-01T00:00:00.000000Z",
     *             "updated_at": "2025-01-01T00:00:00.000000Z"
     *         },
     *         {
     *             "id": 3,
     *             "name": "admin",
     *             "display_name": "Администратор",
     *             "description": "Пользователь с полными правами в системе",
     *             "created_at": "2025-01-01T00:00:00.000000Z",
     *             "updated_at": "2025-01-01T00:00:00.000000Z"
     *         }
     *     ]
     * }
     */
    public function index()
    {
        // Здесь должен быть код для получения списка ролей
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'user',
                    'display_name' => 'Пользователь',
                    'description' => 'Обычный пользователь системы с базовыми правами',
                    'created_at' => '2025-01-01T00:00:00.000000Z',
                    'updated_at' => '2025-01-01T00:00:00.000000Z'
                ],
                [
                    'id' => 2,
                    'name' => 'moderator',
                    'display_name' => 'Модератор',
                    'description' => 'Пользователь с правами модерации контента',
                    'created_at' => '2025-01-01T00:00:00.000000Z',
                    'updated_at' => '2025-01-01T00:00:00.000000Z'
                ],
                [
                    'id' => 3,
                    'name' => 'admin',
                    'display_name' => 'Администратор',
                    'description' => 'Пользователь с полными правами в системе',
                    'created_at' => '2025-01-01T00:00:00.000000Z',
                    'updated_at' => '2025-01-01T00:00:00.000000Z'
                ]
            ]
        ]);
    }

    /**
     * Получение данных роли
     * 
     * Возвращает подробную информацию о роли.
     *
     * @urlParam id integer required ID роли. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "name": "user",
     *         "display_name": "Пользователь",
     *         "description": "Обычный пользователь системы с базовыми правами",
     *         "permissions": [
     *             {
     *                 "id": 1,
     *                 "name": "posts.create",
     *                 "display_name": "Создание постов",
     *                 "description": "Возможность создавать посты"
     *             },
     *             {
     *                 "id": 2,
     *                 "name": "posts.view",
     *                 "display_name": "Просмотр постов",
     *                 "description": "Возможность просматривать посты"
     *             },
     *             {
     *                 "id": 3,
     *                 "name": "comments.create",
     *                 "display_name": "Создание комментариев",
     *                 "description": "Возможность комментировать посты"
     *             }
     *         ],
     *         "users_count": 156,
     *         "created_at": "2025-01-01T00:00:00.000000Z",
     *         "updated_at": "2025-01-01T00:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="роль не найдена" {
     *     "success": false,
     *     "message": "Роль не найдена"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения данных роли
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'user',
                'display_name' => 'Пользователь',
                'description' => 'Обычный пользователь системы с базовыми правами',
                'permissions' => [
                    [
                        'id' => 1,
                        'name' => 'posts.create',
                        'display_name' => 'Создание постов',
                        'description' => 'Возможность создавать посты'
                    ],
                    [
                        'id' => 2,
                        'name' => 'posts.view',
                        'display_name' => 'Просмотр постов',
                        'description' => 'Возможность просматривать посты'
                    ],
                    [
                        'id' => 3,
                        'name' => 'comments.create',
                        'display_name' => 'Создание комментариев',
                        'description' => 'Возможность комментировать посты'
                    ]
                ],
                'users_count' => 156,
                'created_at' => '2025-01-01T00:00:00.000000Z',
                'updated_at' => '2025-01-01T00:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Создание новой роли
     * 
     * Создает новую роль с указанными данными.
     *
     * @bodyParam name string required Уникальное название роли (только латинские буквы и подчеркивания). Example: editor
     * @bodyParam display_name string required Отображаемое название роли. Example: Редактор
     * @bodyParam description string Описание роли. Example: Пользователь с правами редактирования материалов
     * @bodyParam permissions array Массив ID разрешений, которые будут присвоены роли. Example: [1, 2, 3, 4, 5]
     * 
     * @authenticated
     * 
     * @response status=201 scenario="успешное создание" {
     *     "success": true,
     *     "message": "Роль успешно создана",
     *     "data": {
     *         "id": 4,
     *         "name": "editor",
     *         "display_name": "Редактор",
     *         "description": "Пользователь с правами редактирования материалов",
     *         "created_at": "2025-03-30T12:00:00.000000Z",
     *         "updated_at": "2025-03-30T12:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "name": ["Такое название роли уже существует"],
     *         "display_name": ["Поле обязательно для заполнения"]
     *     }
     * }
     */
    public function store(Request $request)
    {
        // Здесь должен быть код для создания роли
        return response()->json([
            'success' => true,
            'message' => 'Роль успешно создана',
            'data' => [
                'id' => 4,
                'name' => 'editor',
                'display_name' => 'Редактор',
                'description' => 'Пользователь с правами редактирования материалов',
                'created_at' => '2025-03-30T12:00:00.000000Z',
                'updated_at' => '2025-03-30T12:00:00.000000Z'
            ]
        ], 201);
    }

    /**
     * Обновление роли
     * 
     * Обновляет данные существующей роли.
     *
     * @urlParam id integer required ID роли. Example: 4
     * 
     * @bodyParam display_name string Отображаемое название роли. Example: Редактор контента
     * @bodyParam description string Описание роли. Example: Пользователь с расширенными правами редактирования материалов
     * @bodyParam permissions array Массив ID разрешений, которые будут присвоены роли. Example: [1, 2, 3, 4, 5, 6]
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное обновление" {
     *     "success": true,
     *     "message": "Роль успешно обновлена",
     *     "data": {
     *         "id": 4,
     *         "name": "editor",
     *         "display_name": "Редактор контента",
     *         "description": "Пользователь с расширенными правами редактирования материалов",
     *         "updated_at": "2025-03-30T13:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="роль не найдена" {
     *     "success": false,
     *     "message": "Роль не найдена"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "display_name": ["Поле обязательно для заполнения"]
     *     }
     * }
     */
    public function update(Request $request, $id)
    {
        // Здесь должен быть код для обновления роли
        return response()->json([
            'success' => true,
            'message' => 'Роль успешно обновлена',
            'data' => [
                'id' => 4,
                'name' => 'editor',
                'display_name' => 'Редактор контента',
                'description' => 'Пользователь с расширенными правами редактирования материалов',
                'updated_at' => '2025-03-30T13:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Удаление роли
     * 
     * Удаляет роль из системы.
     *
     * @urlParam id integer required ID роли. Example: 4
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное удаление" {
     *     "success": true,
     *     "message": "Роль успешно удалена"
     * }
     * 
     * @response status=404 scenario="роль не найдена" {
     *     "success": false,
     *     "message": "Роль не найдена"
     * }
     * 
     * @response status=400 scenario="роль используется" {
     *     "success": false,
     *     "message": "Невозможно удалить роль, которая назначена пользователям"
     * }
     * 
     * @response status=403 scenario="системная роль" {
     *     "success": false,
     *     "message": "Невозможно удалить системную роль"
     * }
     */
    public function destroy($id)
    {
        // Здесь должен быть код для удаления роли
        return response()->json([
            'success' => true,
            'message' => 'Роль успешно удалена'
        ]);
    }

    /**
     * Получение списка разрешений
     * 
     * Возвращает список всех разрешений в системе.
     *
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "posts.create",
     *             "display_name": "Создание постов",
     *             "description": "Возможность создавать посты",
     *             "category": "posts"
     *         },
     *         {
     *             "id": 2,
     *             "name": "posts.edit",
     *             "display_name": "Редактирование постов",
     *             "description": "Возможность редактировать посты",
     *             "category": "posts"
     *         },
     *         {
     *             "id": 3,
     *             "name": "posts.delete",
     *             "display_name": "Удаление постов",
     *             "description": "Возможность удалять посты",
     *             "category": "posts"
     *         },
     *         {
     *             "id": 4,
     *             "name": "comments.moderate",
     *             "display_name": "Модерация комментариев",
     *             "description": "Возможность модерировать комментарии",
     *             "category": "comments"
     *         },
     *         {
     *             "id": 5,
     *             "name": "users.view",
     *             "display_name": "Просмотр пользователей",
     *             "description": "Возможность просматривать данные пользователей",
     *             "category": "users"
     *         }
     *     ]
     * }
     */
    public function permissions()
    {
        // Здесь должен быть код для получения списка разрешений
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'posts.create',
                    'display_name' => 'Создание постов',
                    'description' => 'Возможность создавать посты',
                    'category' => 'posts'
                ],
                [
                    'id' => 2,
                    'name' => 'posts.edit',
                    'display_name' => 'Редактирование постов',
                    'description' => 'Возможность редактировать посты',
                    'category' => 'posts'
                ],
                [
                    'id' => 3,
                    'name' => 'posts.delete',
                    'display_name' => 'Удаление постов',
                    'description' => 'Возможность удалять посты',
                    'category' => 'posts'
                ],
                [
                    'id' => 4,
                    'name' => 'comments.moderate',
                    'display_name' => 'Модерация комментариев',
                    'description' => 'Возможность модерировать комментарии',
                    'category' => 'comments'
                ],
                [
                    'id' => 5,
                    'name' => 'users.view',
                    'display_name' => 'Просмотр пользователей',
                    'description' => 'Возможность просматривать данные пользователей',
                    'category' => 'users'
                ]
            ]
        ]);
    }

    /**
     * Получение списка пользователей с ролью
     * 
     * Возвращает пагинированный список пользователей с указанной ролью.
     *
     * @urlParam id integer required ID роли. Example: 2
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
     *             "id": 2,
     *             "name": "Мария Сидорова",
     *             "email": "maria@example.com",
     *             "status": "active",
     *             "last_login_at": "2025-03-29T16:20:45.000000Z"
     *         },
     *         {
     *             "id": 5,
     *             "name": "Алексей Иванов",
     *             "email": "alex@example.com",
     *             "status": "active",
     *             "last_login_at": "2025-03-28T14:10:22.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 2,
     *         "path": "http://example.com/api/admin/roles/2/users",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 18
     *     }
     * }
     * 
     * @response status=404 scenario="роль не найдена" {
     *     "success": false,
     *     "message": "Роль не найдена"
     * }
     */
    public function users($id)
    {
        // Здесь должен быть код для получения списка пользователей с ролью
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 2,
                    'name' => 'Мария Сидорова',
                    'email' => 'maria@example.com',
                    'status' => 'active',
                    'last_login_at' => '2025-03-29T16:20:45.000000Z'
                ],
                [
                    'id' => 5,
                    'name' => 'Алексей Иванов',
                    'email' => 'alex@example.com',
                    'status' => 'active',
                    'last_login_at' => '2025-03-28T14:10:22.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 2,
                'path' => 'http://example.com/api/admin/roles/2/users',
                'per_page' => 15,
                'to' => 15,
                'total' => 18
            ]
        ]);
    }
} 