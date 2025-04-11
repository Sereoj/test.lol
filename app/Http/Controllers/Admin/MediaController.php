<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Медиа
 * @description Управление медиа-файлами в системе
 */
class MediaController extends Controller
{
    /**
     * Получение списка медиа-файлов
     * 
     * Возвращает список всех медиа-файлов с возможностью фильтрации и пагинации.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * @queryParam search string Поиск по названию файла. Example: презентация
     * @queryParam type string Тип файла (image, video, document, audio). Example: image
     * @queryParam sort string Поле для сортировки (id, name, created_at, size). Example: created_at
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     * @queryParam user_id integer ID пользователя, загрузившего файлы. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "product-image-1.jpg",
     *             "original_name": "My Product Image.jpg",
     *             "type": "image",
     *             "mime_type": "image/jpeg",
     *             "size": 1024567,
     *             "url": "https://example.com/storage/uploads/product-image-1.jpg",
     *             "thumbnail_url": "https://example.com/storage/uploads/thumbnails/product-image-1.jpg",
     *             "user": {
     *                 "id": 1,
     *                 "name": "Иван Петров"
     *             },
     *             "created_at": "2025-03-15T10:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "name": "presentation.pdf",
     *             "original_name": "Company Presentation 2025.pdf",
     *             "type": "document",
     *             "mime_type": "application/pdf",
     *             "size": 2548796,
     *             "url": "https://example.com/storage/uploads/presentation.pdf",
     *             "thumbnail_url": "https://example.com/storage/uploads/thumbnails/presentation.pdf.jpg",
     *             "user": {
     *                 "id": 2,
     *                 "name": "Мария Сидорова"
     *             },
     *             "created_at": "2025-03-20T14:30:00.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 10,
     *         "path": "http://example.com/api/admin/media",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 145
     *     }
     * }
     */
    public function index(Request $request)
    {
        // Здесь должен быть код для получения списка медиа-файлов
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'product-image-1.jpg',
                    'original_name' => 'My Product Image.jpg',
                    'type' => 'image',
                    'mime_type' => 'image/jpeg',
                    'size' => 1024567,
                    'url' => 'https://example.com/storage/uploads/product-image-1.jpg',
                    'thumbnail_url' => 'https://example.com/storage/uploads/thumbnails/product-image-1.jpg',
                    'user' => [
                        'id' => 1,
                        'name' => 'Иван Петров'
                    ],
                    'created_at' => '2025-03-15T10:00:00.000000Z'
                ],
                [
                    'id' => 2,
                    'name' => 'presentation.pdf',
                    'original_name' => 'Company Presentation 2025.pdf',
                    'type' => 'document',
                    'mime_type' => 'application/pdf',
                    'size' => 2548796,
                    'url' => 'https://example.com/storage/uploads/presentation.pdf',
                    'thumbnail_url' => 'https://example.com/storage/uploads/thumbnails/presentation.pdf.jpg',
                    'user' => [
                        'id' => 2,
                        'name' => 'Мария Сидорова'
                    ],
                    'created_at' => '2025-03-20T14:30:00.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 10,
                'path' => 'http://example.com/api/admin/media',
                'per_page' => 15,
                'to' => 15,
                'total' => 145
            ]
        ]);
    }

    /**
     * Получение информации о медиа-файле
     * 
     * Возвращает детальную информацию о медиа-файле.
     *
     * @urlParam id integer required ID медиа-файла. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "name": "product-image-1.jpg",
     *         "original_name": "My Product Image.jpg",
     *         "type": "image",
     *         "mime_type": "image/jpeg",
     *         "size": 1024567,
     *         "dimensions": {
     *             "width": 1920,
     *             "height": 1080
     *         },
     *         "url": "https://example.com/storage/uploads/product-image-1.jpg",
     *         "thumbnail_url": "https://example.com/storage/uploads/thumbnails/product-image-1.jpg",
     *         "user": {
     *             "id": 1,
     *             "name": "Иван Петров",
     *             "email": "ivan@example.com"
     *         },
     *         "usage": {
     *             "posts": [
     *                 {
     *                     "id": 15,
     *                     "title": "Новый продукт компании",
     *                     "url": "https://example.com/posts/15"
     *                 }
     *             ],
     *             "users": [
     *                 {
     *                     "id": 3,
     *                     "name": "Алексей Николаев",
     *                     "usage_type": "avatar"
     *                 }
     *             ]
     *         },
     *         "meta": {
     *             "alt": "Изображение нового продукта",
     *             "title": "Новый инновационный продукт 2025",
     *             "description": "Фотография продукта с выставки технологий"
     *         },
     *         "created_at": "2025-03-15T10:00:00.000000Z",
     *         "updated_at": "2025-03-15T10:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="файл не найден" {
     *     "success": false,
     *     "message": "Медиа-файл не найден"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения информации о медиа-файле
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'product-image-1.jpg',
                'original_name' => 'My Product Image.jpg',
                'type' => 'image',
                'mime_type' => 'image/jpeg',
                'size' => 1024567,
                'dimensions' => [
                    'width' => 1920,
                    'height' => 1080
                ],
                'url' => 'https://example.com/storage/uploads/product-image-1.jpg',
                'thumbnail_url' => 'https://example.com/storage/uploads/thumbnails/product-image-1.jpg',
                'user' => [
                    'id' => 1,
                    'name' => 'Иван Петров',
                    'email' => 'ivan@example.com'
                ],
                'usage' => [
                    'posts' => [
                        [
                            'id' => 15,
                            'title' => 'Новый продукт компании',
                            'url' => 'https://example.com/posts/15'
                        ]
                    ],
                    'users' => [
                        [
                            'id' => 3,
                            'name' => 'Алексей Николаев',
                            'usage_type' => 'avatar'
                        ]
                    ]
                ],
                'meta' => [
                    'alt' => 'Изображение нового продукта',
                    'title' => 'Новый инновационный продукт 2025',
                    'description' => 'Фотография продукта с выставки технологий'
                ],
                'created_at' => '2025-03-15T10:00:00.000000Z',
                'updated_at' => '2025-03-15T10:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Удаление медиа-файла
     * 
     * Удаляет медиа-файл из системы.
     *
     * @urlParam id integer required ID медиа-файла. Example: 1
     * 
     * @bodyParam force boolean Принудительное удаление, даже если файл используется. Example: false
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное удаление" {
     *     "success": true,
     *     "message": "Медиа-файл успешно удален"
     * }
     * 
     * @response status=404 scenario="файл не найден" {
     *     "success": false,
     *     "message": "Медиа-файл не найден"
     * }
     * 
     * @response status=400 scenario="файл используется" {
     *     "success": false,
     *     "message": "Невозможно удалить файл, так как он используется в системе",
     *     "data": {
     *         "usage": {
     *             "posts": [
     *                 {
     *                     "id": 15,
     *                     "title": "Новый продукт компании"
     *                 }
     *             ],
     *             "users": [
     *                 {
     *                     "id": 3,
     *                     "name": "Алексей Николаев",
     *                     "usage_type": "avatar"
     *                 }
     *             ]
     *         }
     *     }
     * }
     */
    public function destroy(Request $request, $id)
    {
        // Здесь должен быть код для удаления медиа-файла
        return response()->json([
            'success' => true,
            'message' => 'Медиа-файл успешно удален'
        ]);
    }
} 