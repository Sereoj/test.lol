<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Теги
 * @description Управление тегами для постов
 */
class TagController extends Controller
{
    /**
     * Получение списка тегов
     * 
     * Возвращает список всех тегов с возможностью поиска и пагинации.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * @queryParam search string Поиск по названию тега. Example: технологии
     * @queryParam sort string Поле для сортировки (id, name, created_at, posts_count). Example: posts_count
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "Технологии",
     *             "slug": "tech",
     *             "posts_count": 350,
     *             "created_at": "2025-01-01T00:00:00.000000Z",
     *             "updated_at": "2025-01-01T00:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "name": "Бизнес",
     *             "slug": "business",
     *             "posts_count": 120,
     *             "created_at": "2025-01-01T00:00:00.000000Z",
     *             "updated_at": "2025-01-01T00:00:00.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 2,
     *         "path": "http://example.com/api/admin/tags",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 18
     *     }
     * }
     */
    public function index(Request $request)
    {
        // Здесь должен быть код для получения списка тегов
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Технологии',
                    'slug' => 'tech',
                    'posts_count' => 350,
                    'created_at' => '2025-01-01T00:00:00.000000Z',
                    'updated_at' => '2025-01-01T00:00:00.000000Z'
                ],
                [
                    'id' => 2,
                    'name' => 'Бизнес',
                    'slug' => 'business',
                    'posts_count' => 120,
                    'created_at' => '2025-01-01T00:00:00.000000Z',
                    'updated_at' => '2025-01-01T00:00:00.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 2,
                'path' => 'http://example.com/api/admin/tags',
                'per_page' => 15,
                'to' => 15,
                'total' => 18
            ]
        ]);
    }

    /**
     * Получение данных тега
     * 
     * Возвращает подробную информацию о теге.
     *
     * @urlParam id integer required ID тега. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "name": "Технологии",
     *         "slug": "tech",
     *         "description": "Новости и статьи о технологиях",
     *         "meta_title": "Технологические новости и статьи",
     *         "meta_description": "Самые актуальные новости из мира технологий",
     *         "posts_count": 350,
     *         "related_tags": [
     *             {
     *                 "id": 3,
     *                 "name": "Искусственный интеллект",
     *                 "slug": "ai",
     *                 "posts_count": 120
     *             },
     *             {
     *                 "id": 4,
     *                 "name": "Программирование",
     *                 "slug": "programming",
     *                 "posts_count": 85
     *             }
     *         ],
     *         "created_at": "2025-01-01T00:00:00.000000Z",
     *         "updated_at": "2025-01-01T00:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="тег не найден" {
     *     "success": false,
     *     "message": "Тег не найден"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения данных тега
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'Технологии',
                'slug' => 'tech',
                'description' => 'Новости и статьи о технологиях',
                'meta_title' => 'Технологические новости и статьи',
                'meta_description' => 'Самые актуальные новости из мира технологий',
                'posts_count' => 350,
                'related_tags' => [
                    [
                        'id' => 3,
                        'name' => 'Искусственный интеллект',
                        'slug' => 'ai',
                        'posts_count' => 120
                    ],
                    [
                        'id' => 4,
                        'name' => 'Программирование',
                        'slug' => 'programming',
                        'posts_count' => 85
                    ]
                ],
                'created_at' => '2025-01-01T00:00:00.000000Z',
                'updated_at' => '2025-01-01T00:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Создание нового тега
     * 
     * Создает новый тег с указанными данными.
     *
     * @bodyParam name string required Название тега. Example: Здоровье
     * @bodyParam slug string Уникальный идентификатор тега (если не указан, генерируется из названия). Example: health
     * @bodyParam description string Описание тега. Example: Статьи о здоровом образе жизни
     * @bodyParam meta_title string Мета-заголовок для SEO. Example: Здоровье и здоровый образ жизни
     * @bodyParam meta_description string Мета-описание для SEO. Example: Статьи о здоровье, правильном питании и фитнесе
     * 
     * @authenticated
     * 
     * @response status=201 scenario="успешное создание" {
     *     "success": true,
     *     "message": "Тег успешно создан",
     *     "data": {
     *         "id": 10,
     *         "name": "Здоровье",
     *         "slug": "health",
     *         "description": "Статьи о здоровом образе жизни",
     *         "created_at": "2025-03-30T12:00:00.000000Z",
     *         "updated_at": "2025-03-30T12:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "name": ["Такое название тега уже существует"],
     *         "slug": ["Такой slug уже используется"]
     *     }
     * }
     */
    public function store(Request $request)
    {
        // Здесь должен быть код для создания тега
        return response()->json([
            'success' => true,
            'message' => 'Тег успешно создан',
            'data' => [
                'id' => 10,
                'name' => 'Здоровье',
                'slug' => 'health',
                'description' => 'Статьи о здоровом образе жизни',
                'created_at' => '2025-03-30T12:00:00.000000Z',
                'updated_at' => '2025-03-30T12:00:00.000000Z'
            ]
        ], 201);
    }

    /**
     * Обновление тега
     * 
     * Обновляет данные существующего тега.
     *
     * @urlParam id integer required ID тега. Example: 1
     * 
     * @bodyParam name string Название тега. Example: Современные технологии
     * @bodyParam slug string Уникальный идентификатор тега. Example: modern-tech
     * @bodyParam description string Описание тега. Example: Обновленное описание тега
     * @bodyParam meta_title string Мета-заголовок для SEO. Example: Новости о современных технологиях
     * @bodyParam meta_description string Мета-описание для SEO. Example: Актуальные новости из мира современных технологий
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное обновление" {
     *     "success": true,
     *     "message": "Тег успешно обновлен",
     *     "data": {
     *         "id": 1,
     *         "name": "Современные технологии",
     *         "slug": "modern-tech",
     *         "description": "Обновленное описание тега",
     *         "updated_at": "2025-03-30T13:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="тег не найден" {
     *     "success": false,
     *     "message": "Тег не найден"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "slug": ["Такой slug уже используется"]
     *     }
     * }
     */
    public function update(Request $request, $id)
    {
        // Здесь должен быть код для обновления тега
        return response()->json([
            'success' => true,
            'message' => 'Тег успешно обновлен',
            'data' => [
                'id' => 1,
                'name' => 'Современные технологии',
                'slug' => 'modern-tech',
                'description' => 'Обновленное описание тега',
                'updated_at' => '2025-03-30T13:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Удаление тега
     * 
     * Удаляет тег из системы.
     *
     * @urlParam id integer required ID тега. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное удаление" {
     *     "success": true,
     *     "message": "Тег успешно удален"
     * }
     * 
     * @response status=404 scenario="тег не найден" {
     *     "success": false,
     *     "message": "Тег не найден"
     * }
     */
    public function destroy($id)
    {
        // Здесь должен быть код для удаления тега
        return response()->json([
            'success' => true,
            'message' => 'Тег успешно удален'
        ]);
    }
} 