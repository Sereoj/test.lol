<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Посты
 * @description Управление постами в системе
 */
class PostController extends Controller
{
    /**
     * Получение списка постов
     * 
     * Возвращает пагинированный список постов с возможностью фильтрации и сортировки.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * @queryParam search string Поиск по заголовку или содержимому поста. Example: новости
     * @queryParam sort string Поле для сортировки (id, title, created_at, views). Example: created_at
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     * @queryParam status string Фильтр по статусу (published, draft, pending). Example: published
     * @queryParam user_id integer Фильтр по ID автора. Example: 2
     * @queryParam category_id integer Фильтр по ID категории. Example: 3
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "title": "Первый пост",
     *             "excerpt": "Краткое описание первого поста...",
     *             "status": "published",
     *             "author": {
     *                 "id": 2,
     *                 "name": "Мария Сидорова"
     *             },
     *             "views": 156,
     *             "comments_count": 8,
     *             "likes_count": 24,
     *             "created_at": "2025-03-15T10:00:00.000000Z",
     *             "published_at": "2025-03-15T12:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "title": "Второй пост",
     *             "excerpt": "Краткое описание второго поста...",
     *             "status": "published",
     *             "author": {
     *                 "id": 1,
     *                 "name": "Иван Петров"
     *             },
     *             "views": 78,
     *             "comments_count": 3,
     *             "likes_count": 12,
     *             "created_at": "2025-03-16T09:30:00.000000Z",
     *             "published_at": "2025-03-16T11:45:00.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 10,
     *         "path": "http://example.com/api/admin/posts",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 150
     *     }
     * }
     */
    public function index(Request $request)
    {
        // Здесь должен быть код для получения списка постов
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Первый пост',
                    'excerpt' => 'Краткое описание первого поста...',
                    'status' => 'published',
                    'author' => [
                        'id' => 2,
                        'name' => 'Мария Сидорова'
                    ],
                    'views' => 156,
                    'comments_count' => 8,
                    'likes_count' => 24,
                    'created_at' => '2025-03-15T10:00:00.000000Z',
                    'published_at' => '2025-03-15T12:00:00.000000Z'
                ],
                [
                    'id' => 2,
                    'title' => 'Второй пост',
                    'excerpt' => 'Краткое описание второго поста...',
                    'status' => 'published',
                    'author' => [
                        'id' => 1,
                        'name' => 'Иван Петров'
                    ],
                    'views' => 78,
                    'comments_count' => 3,
                    'likes_count' => 12,
                    'created_at' => '2025-03-16T09:30:00.000000Z',
                    'published_at' => '2025-03-16T11:45:00.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 10,
                'path' => 'http://example.com/api/admin/posts',
                'per_page' => 15,
                'to' => 15,
                'total' => 150
            ]
        ]);
    }

    /**
     * Получение данных поста
     * 
     * Возвращает подробную информацию о посте.
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "title": "Первый пост",
     *         "content": "Полное содержание первого поста...",
     *         "excerpt": "Краткое описание первого поста...",
     *         "status": "published",
     *         "author": {
     *             "id": 2,
     *             "name": "Мария Сидорова",
     *             "email": "maria@example.com"
     *         },
     *         "categories": [
     *             {
     *                 "id": 1,
     *                 "name": "Новости"
     *             },
     *             {
     *                 "id": 3,
     *                 "name": "Технологии"
     *             }
     *         ],
     *         "tags": ["новости", "технологии", "обновления"],
     *         "meta": {
     *             "title": "SEO заголовок для первого поста",
     *             "description": "SEO описание для первого поста",
     *             "keywords": "пост, новости, статья"
     *         },
     *         "featured_image": "https://example.com/images/posts/1/featured.jpg",
     *         "gallery": [
     *             "https://example.com/images/posts/1/gallery/1.jpg",
     *             "https://example.com/images/posts/1/gallery/2.jpg"
     *         ],
     *         "views": 156,
     *         "comments_count": 8,
     *         "likes_count": 24,
     *         "created_at": "2025-03-15T10:00:00.000000Z",
     *         "updated_at": "2025-03-20T14:30:00.000000Z",
     *         "published_at": "2025-03-15T12:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения данных поста
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'title' => 'Первый пост',
                'content' => 'Полное содержание первого поста...',
                'excerpt' => 'Краткое описание первого поста...',
                'status' => 'published',
                'author' => [
                    'id' => 2,
                    'name' => 'Мария Сидорова',
                    'email' => 'maria@example.com'
                ],
                'categories' => [
                    [
                        'id' => 1,
                        'name' => 'Новости'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Технологии'
                    ]
                ],
                'tags' => ['новости', 'технологии', 'обновления'],
                'meta' => [
                    'title' => 'SEO заголовок для первого поста',
                    'description' => 'SEO описание для первого поста',
                    'keywords' => 'пост, новости, статья'
                ],
                'featured_image' => 'https://example.com/images/posts/1/featured.jpg',
                'gallery' => [
                    'https://example.com/images/posts/1/gallery/1.jpg',
                    'https://example.com/images/posts/1/gallery/2.jpg'
                ],
                'views' => 156,
                'comments_count' => 8,
                'likes_count' => 24,
                'created_at' => '2025-03-15T10:00:00.000000Z',
                'updated_at' => '2025-03-20T14:30:00.000000Z',
                'published_at' => '2025-03-15T12:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Создание нового поста
     * 
     * Создает новый пост с указанными данными.
     *
     * @bodyParam title string required Заголовок поста. Example: Новый пост
     * @bodyParam content string required Содержание поста. Example: Текст нового поста...
     * @bodyParam excerpt string Краткое описание поста. Example: Краткое описание нового поста...
     * @bodyParam status string Статус поста (published, draft, pending). Example: draft
     * @bodyParam user_id integer ID автора поста (если отличается от текущего пользователя). Example: 2
     * @bodyParam categories array Массив ID категорий поста. Example: [1, 3]
     * @bodyParam tags array Массив тегов поста. Example: ["новости", "обновления"]
     * @bodyParam meta object Meta-данные для SEO.
     * @bodyParam meta.title string SEO заголовок. Example: SEO заголовок для нового поста
     * @bodyParam meta.description string SEO описание. Example: SEO описание для нового поста
     * @bodyParam meta.keywords string SEO ключевые слова. Example: пост, новости, статья
     * @bodyParam featured_image file Главное изображение поста.
     * @bodyParam gallery array Массив дополнительных изображений для поста.
     * @bodyParam published_at datetime Дата и время публикации. Example: 2025-04-01 12:00:00
     * 
     * @authenticated
     * 
     * @response status=201 scenario="успешное создание" {
     *     "success": true,
     *     "message": "Пост успешно создан",
     *     "data": {
     *         "id": 3,
     *         "title": "Новый пост",
     *         "status": "draft",
     *         "author": {
     *             "id": 1,
     *             "name": "Админ Администраторов"
     *         },
     *         "created_at": "2025-03-30T12:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "title": ["Поле обязательно для заполнения"],
     *         "content": ["Поле обязательно для заполнения"]
     *     }
     * }
     */
    public function store(Request $request)
    {
        // Здесь должен быть код для создания поста
        return response()->json([
            'success' => true,
            'message' => 'Пост успешно создан',
            'data' => [
                'id' => 3,
                'title' => 'Новый пост',
                'status' => 'draft',
                'author' => [
                    'id' => 1,
                    'name' => 'Админ Администраторов'
                ],
                'created_at' => '2025-03-30T12:00:00.000000Z'
            ]
        ], 201);
    }

    /**
     * Обновление поста
     * 
     * Обновляет данные существующего поста.
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @bodyParam title string Заголовок поста. Example: Обновленный заголовок
     * @bodyParam content string Содержание поста. Example: Обновленный текст поста...
     * @bodyParam excerpt string Краткое описание поста. Example: Краткое описание обновленного поста...
     * @bodyParam status string Статус поста (published, draft, pending). Example: published
     * @bodyParam categories array Массив ID категорий поста. Example: [1, 2, 3]
     * @bodyParam tags array Массив тегов поста. Example: ["новости", "обновления", "важное"]
     * @bodyParam meta object Meta-данные для SEO.
     * @bodyParam meta.title string SEO заголовок. Example: Обновленный SEO заголовок
     * @bodyParam meta.description string SEO описание. Example: Обновленное SEO описание
     * @bodyParam meta.keywords string SEO ключевые слова. Example: пост, новости, статья, обновление
     * @bodyParam featured_image file Главное изображение поста.
     * @bodyParam gallery array Массив дополнительных изображений для поста.
     * @bodyParam published_at datetime Дата и время публикации. Example: 2025-04-01 15:30:00
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное обновление" {
     *     "success": true,
     *     "message": "Пост успешно обновлен",
     *     "data": {
     *         "id": 1,
     *         "title": "Обновленный заголовок",
     *         "status": "published",
     *         "updated_at": "2025-03-30T13:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "title": ["Заголовок должен содержать не менее 5 символов"]
     *     }
     * }
     */
    public function update(Request $request, $id)
    {
        // Здесь должен быть код для обновления поста
        return response()->json([
            'success' => true,
            'message' => 'Пост успешно обновлен',
            'data' => [
                'id' => 1,
                'title' => 'Обновленный заголовок',
                'status' => 'published',
                'updated_at' => '2025-03-30T13:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Удаление поста
     * 
     * Удаляет пост из системы.
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное удаление" {
     *     "success": true,
     *     "message": "Пост успешно удален"
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     * 
     * @response status=403 scenario="недостаточно прав" {
     *     "success": false,
     *     "message": "Недостаточно прав для удаления этого поста"
     * }
     */
    public function destroy($id)
    {
        // Здесь должен быть код для удаления поста
        return response()->json([
            'success' => true,
            'message' => 'Пост успешно удален'
        ]);
    }

    /**
     * Публикация поста
     * 
     * Публикует пост (меняет статус на "published").
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная публикация" {
     *     "success": true,
     *     "message": "Пост опубликован",
     *     "data": {
     *         "id": 1,
     *         "status": "published",
     *         "published_at": "2025-03-30T14:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     * 
     * @response status=400 scenario="пост уже опубликован" {
     *     "success": false,
     *     "message": "Пост уже опубликован"
     * }
     */
    public function publish($id)
    {
        // Здесь должен быть код для публикации поста
        return response()->json([
            'success' => true,
            'message' => 'Пост опубликован',
            'data' => [
                'id' => 1,
                'status' => 'published',
                'published_at' => '2025-03-30T14:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Отмена публикации поста
     * 
     * Отменяет публикацию поста (меняет статус на "draft").
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная отмена публикации" {
     *     "success": true,
     *     "message": "Публикация поста отменена",
     *     "data": {
     *         "id": 1,
     *         "status": "draft",
     *         "published_at": null
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     * 
     * @response status=400 scenario="пост не опубликован" {
     *     "success": false,
     *     "message": "Пост не опубликован"
     * }
     */
    public function unpublish($id)
    {
        // Здесь должен быть код для отмены публикации поста
        return response()->json([
            'success' => true,
            'message' => 'Публикация поста отменена',
            'data' => [
                'id' => 1,
                'status' => 'draft',
                'published_at' => null
            ]
        ]);
    }

    /**
     * Отправка поста на модерацию
     * 
     * Отправляет пост на модерацию (меняет статус на "pending").
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная отправка на модерацию" {
     *     "success": true,
     *     "message": "Пост отправлен на модерацию",
     *     "data": {
     *         "id": 1,
     *         "status": "pending"
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     */
    public function sendToModeration($id)
    {
        // Здесь должен быть код для отправки поста на модерацию
        return response()->json([
            'success' => true,
            'message' => 'Пост отправлен на модерацию',
            'data' => [
                'id' => 1,
                'status' => 'pending'
            ]
        ]);
    }

    /**
     * Получение комментариев к посту
     * 
     * Возвращает пагинированный список комментариев к посту.
     *
     * @urlParam id integer required ID поста. Example: 1
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
     *             "id": 1,
     *             "content": "Отличный пост! Очень полезная информация.",
     *             "author": {
     *                 "id": 3,
     *                 "name": "Александр Николаев"
     *             },
     *             "created_at": "2025-03-15T14:20:00.000000Z",
     *             "status": "approved"
     *         },
     *         {
     *             "id": 2,
     *             "content": "Спасибо за интересную статью!",
     *             "author": {
     *                 "id": 4,
     *                 "name": "Екатерина Смирнова"
     *             },
     *             "created_at": "2025-03-15T15:30:00.000000Z",
     *             "status": "approved"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 1,
     *         "path": "http://example.com/api/admin/posts/1/comments",
     *         "per_page": 15,
     *         "to": 8,
     *         "total": 8
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     */
    public function comments($id)
    {
        // Здесь должен быть код для получения комментариев к посту
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'content' => 'Отличный пост! Очень полезная информация.',
                    'author' => [
                        'id' => 3,
                        'name' => 'Александр Николаев'
                    ],
                    'created_at' => '2025-03-15T14:20:00.000000Z',
                    'status' => 'approved'
                ],
                [
                    'id' => 2,
                    'content' => 'Спасибо за интересную статью!',
                    'author' => [
                        'id' => 4,
                        'name' => 'Екатерина Смирнова'
                    ],
                    'created_at' => '2025-03-15T15:30:00.000000Z',
                    'status' => 'approved'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 1,
                'path' => 'http://example.com/api/admin/posts/1/comments',
                'per_page' => 15,
                'to' => 8,
                'total' => 8
            ]
        ]);
    }

    /**
     * Одобрение поста
     * 
     * Одобряет пост после модерации (меняет статус на "published").
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @bodyParam comment string Комментарий модератора. Example: Материал соответствует требованиям
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное одобрение" {
     *     "success": true,
     *     "message": "Пост одобрен",
     *     "data": {
     *         "id": 1,
     *         "status": "published",
     *         "published_at": "2025-03-30T14:00:00.000000Z",
     *         "moderation": {
     *             "approved_by": {
     *                 "id": 2,
     *                 "name": "Модератор Модераторов"
     *             },
     *             "approved_at": "2025-03-30T14:00:00.000000Z",
     *             "comment": "Материал соответствует требованиям"
     *         }
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     * 
     * @response status=400 scenario="пост уже одобрен" {
     *     "success": false,
     *     "message": "Пост уже опубликован"
     * }
     */
    public function approve(Request $request, $id)
    {
        // Здесь должен быть код для одобрения поста
        return response()->json([
            'success' => true,
            'message' => 'Пост одобрен',
            'data' => [
                'id' => 1,
                'status' => 'published',
                'published_at' => '2025-03-30T14:00:00.000000Z',
                'moderation' => [
                    'approved_by' => [
                        'id' => 2,
                        'name' => 'Модератор Модераторов'
                    ],
                    'approved_at' => '2025-03-30T14:00:00.000000Z',
                    'comment' => 'Материал соответствует требованиям'
                ]
            ]
        ]);
    }

    /**
     * Отклонение поста
     * 
     * Отклоняет пост после модерации (меняет статус на "rejected").
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @bodyParam reason string required Причина отклонения. Example: Нарушение правил сообщества
     * @bodyParam comment string Подробный комментарий модератора. Example: Материал содержит недопустимый контент
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное отклонение" {
     *     "success": true,
     *     "message": "Пост отклонен",
     *     "data": {
     *         "id": 1,
     *         "status": "rejected",
     *         "moderation": {
     *             "rejected_by": {
     *                 "id": 2,
     *                 "name": "Модератор Модераторов"
     *             },
     *             "rejected_at": "2025-03-30T14:30:00.000000Z",
     *             "reason": "Нарушение правил сообщества",
     *             "comment": "Материал содержит недопустимый контент"
     *         }
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
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
    public function reject(Request $request, $id)
    {
        // Здесь должен быть код для отклонения поста
        return response()->json([
            'success' => true,
            'message' => 'Пост отклонен',
            'data' => [
                'id' => 1,
                'status' => 'rejected',
                'moderation' => [
                    'rejected_by' => [
                        'id' => 2,
                        'name' => 'Модератор Модераторов'
                    ],
                    'rejected_at' => '2025-03-30T14:30:00.000000Z',
                    'reason' => 'Нарушение правил сообщества',
                    'comment' => 'Материал содержит недопустимый контент'
                ]
            ]
        ]);
    }

    /**
     * Выделение поста
     * 
     * Отмечает пост как рекомендуемый/избранный материал.
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @bodyParam featured_until date Дата, до которой пост будет выделен (null - бессрочно). Example: 2025-04-30
     * @bodyParam priority integer Приоритет отображения (1-10). Example: 5
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное выделение" {
     *     "success": true,
     *     "message": "Пост выделен",
     *     "data": {
     *         "id": 1,
     *         "is_featured": true,
     *         "featured_until": "2025-04-30T23:59:59.000000Z",
     *         "featured_priority": 5,
     *         "featured_at": "2025-03-30T15:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     * 
     * @response status=400 scenario="пост уже выделен" {
     *     "success": false,
     *     "message": "Пост уже выделен"
     * }
     */
    public function feature(Request $request, $id)
    {
        // Здесь должен быть код для выделения поста
        return response()->json([
            'success' => true,
            'message' => 'Пост выделен',
            'data' => [
                'id' => 1,
                'is_featured' => true,
                'featured_until' => '2025-04-30T23:59:59.000000Z',
                'featured_priority' => 5,
                'featured_at' => '2025-03-30T15:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Отмена выделения поста
     * 
     * Снимает с поста отметку о рекомендуемом/избранном материале.
     *
     * @urlParam id integer required ID поста. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная отмена выделения" {
     *     "success": true,
     *     "message": "Выделение поста отменено",
     *     "data": {
     *         "id": 1,
     *         "is_featured": false,
     *         "featured_until": null,
     *         "featured_priority": null
     *     }
     * }
     * 
     * @response status=404 scenario="пост не найден" {
     *     "success": false,
     *     "message": "Пост не найден"
     * }
     * 
     * @response status=400 scenario="пост не выделен" {
     *     "success": false,
     *     "message": "Пост не выделен"
     * }
     */
    public function unfeature($id)
    {
        // Здесь должен быть код для отмены выделения поста
        return response()->json([
            'success' => true,
            'message' => 'Выделение поста отменено',
            'data' => [
                'id' => 1,
                'is_featured' => false,
                'featured_until' => null,
                'featured_priority' => null
            ]
        ]);
    }
} 