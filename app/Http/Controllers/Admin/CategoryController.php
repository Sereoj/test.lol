<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Категории
 * @description Управление категориями постов
 */
class CategoryController extends Controller
{
    /**
     * Получение списка категорий
     * 
     * Возвращает список всех категорий с возможностью поиска и пагинации.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * @queryParam search string Поиск по названию категории. Example: технологии
     * @queryParam sort string Поле для сортировки (id, name, created_at). Example: name
     * @queryParam order string Направление сортировки (asc, desc). Example: asc
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "Бизнес",
     *             "slug": "business",
     *             "description": "Статьи о бизнесе и предпринимательстве",
     *             "posts_count": 120,
     *             "created_at": "2025-01-01T00:00:00.000000Z",
     *             "updated_at": "2025-01-01T00:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "name": "Технологии",
     *             "slug": "tech",
     *             "description": "Новости из мира технологий",
     *             "posts_count": 350,
     *             "created_at": "2025-01-01T00:00:00.000000Z",
     *             "updated_at": "2025-01-01T00:00:00.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 2,
     *         "path": "http://example.com/api/admin/categories",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 18
     *     }
     * }
     */
    public function index(Request $request)
    {
        // Здесь должен быть код для получения списка категорий
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Бизнес',
                    'slug' => 'business',
                    'description' => 'Статьи о бизнесе и предпринимательстве',
                    'posts_count' => 120,
                    'created_at' => '2025-01-01T00:00:00.000000Z',
                    'updated_at' => '2025-01-01T00:00:00.000000Z'
                ],
                [
                    'id' => 2,
                    'name' => 'Технологии',
                    'slug' => 'tech',
                    'description' => 'Новости из мира технологий',
                    'posts_count' => 350,
                    'created_at' => '2025-01-01T00:00:00.000000Z',
                    'updated_at' => '2025-01-01T00:00:00.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 2,
                'path' => 'http://example.com/api/admin/categories',
                'per_page' => 15,
                'to' => 15,
                'total' => 18
            ]
        ]);
    }

    /**
     * Получение данных категории
     * 
     * Возвращает подробную информацию о категории.
     *
     * @urlParam id integer required ID категории. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "name": "Бизнес",
     *         "slug": "business",
     *         "description": "Статьи о бизнесе и предпринимательстве",
     *         "meta_title": "Бизнес новости и статьи",
     *         "meta_description": "Лучшие статьи о бизнесе и предпринимательстве",
     *         "posts_count": 120,
     *         "parent_id": null,
     *         "children": [
     *             {
     *                 "id": 5,
     *                 "name": "Стартапы",
     *                 "slug": "startups",
     *                 "posts_count": 45
     *             },
     *             {
     *                 "id": 6,
     *                 "name": "Маркетинг",
     *                 "slug": "marketing",
     *                 "posts_count": 30
     *             }
     *         ],
     *         "created_at": "2025-01-01T00:00:00.000000Z",
     *         "updated_at": "2025-01-01T00:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="категория не найдена" {
     *     "success": false,
     *     "message": "Категория не найдена"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения данных категории
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'Бизнес',
                'slug' => 'business',
                'description' => 'Статьи о бизнесе и предпринимательстве',
                'meta_title' => 'Бизнес новости и статьи',
                'meta_description' => 'Лучшие статьи о бизнесе и предпринимательстве',
                'posts_count' => 120,
                'parent_id' => null,
                'children' => [
                    [
                        'id' => 5,
                        'name' => 'Стартапы',
                        'slug' => 'startups',
                        'posts_count' => 45
                    ],
                    [
                        'id' => 6,
                        'name' => 'Маркетинг',
                        'slug' => 'marketing',
                        'posts_count' => 30
                    ]
                ],
                'created_at' => '2025-01-01T00:00:00.000000Z',
                'updated_at' => '2025-01-01T00:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Создание новой категории
     * 
     * Создает новую категорию с указанными данными.
     *
     * @bodyParam name string required Название категории. Example: Здоровье
     * @bodyParam slug string Уникальный идентификатор категории (если не указан, генерируется из названия). Example: health
     * @bodyParam description string Описание категории. Example: Статьи о здоровом образе жизни
     * @bodyParam meta_title string Мета-заголовок для SEO. Example: Здоровье и здоровый образ жизни
     * @bodyParam meta_description string Мета-описание для SEO. Example: Статьи о здоровье, правильном питании и фитнесе
     * @bodyParam parent_id integer ID родительской категории (если это подкатегория). Example: null
     * 
     * @authenticated
     * 
     * @response status=201 scenario="успешное создание" {
     *     "success": true,
     *     "message": "Категория успешно создана",
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
     *         "name": ["Такое название категории уже существует"],
     *         "slug": ["Такой slug уже используется"]
     *     }
     * }
     */
    public function store(Request $request)
    {
        // Здесь должен быть код для создания категории
        return response()->json([
            'success' => true,
            'message' => 'Категория успешно создана',
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
     * Обновление категории
     * 
     * Обновляет данные существующей категории.
     *
     * @urlParam id integer required ID категории. Example: 1
     * 
     * @bodyParam name string Название категории. Example: Бизнес и финансы
     * @bodyParam slug string Уникальный идентификатор категории. Example: business-finance
     * @bodyParam description string Описание категории. Example: Обновленное описание категории
     * @bodyParam meta_title string Мета-заголовок для SEO. Example: Новости бизнеса и финансов
     * @bodyParam meta_description string Мета-описание для SEO. Example: Актуальные новости из мира бизнеса и финансов
     * @bodyParam parent_id integer ID родительской категории (если это подкатегория). Example: null
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное обновление" {
     *     "success": true,
     *     "message": "Категория успешно обновлена",
     *     "data": {
     *         "id": 1,
     *         "name": "Бизнес и финансы",
     *         "slug": "business-finance",
     *         "description": "Обновленное описание категории",
     *         "updated_at": "2025-03-30T13:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="категория не найдена" {
     *     "success": false,
     *     "message": "Категория не найдена"
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
        // Здесь должен быть код для обновления категории
        return response()->json([
            'success' => true,
            'message' => 'Категория успешно обновлена',
            'data' => [
                'id' => 1,
                'name' => 'Бизнес и финансы',
                'slug' => 'business-finance',
                'description' => 'Обновленное описание категории',
                'updated_at' => '2025-03-30T13:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Удаление категории
     * 
     * Удаляет категорию из системы.
     *
     * @urlParam id integer required ID категории. Example: 1
     * 
     * @bodyParam move_posts_to integer ID категории, в которую будут перемещены посты удаляемой категории. Example: 2
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное удаление" {
     *     "success": true,
     *     "message": "Категория успешно удалена"
     * }
     * 
     * @response status=404 scenario="категория не найдена" {
     *     "success": false,
     *     "message": "Категория не найдена"
     * }
     * 
     * @response status=400 scenario="нельзя удалить" {
     *     "success": false,
     *     "message": "Нельзя удалить категорию с подкатегориями"
     * }
     */
    public function destroy(Request $request, $id)
    {
        // Здесь должен быть код для удаления категории
        return response()->json([
            'success' => true,
            'message' => 'Категория успешно удалена'
        ]);
    }
} 