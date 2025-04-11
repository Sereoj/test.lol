<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Дашборд
 * @description Управление дашбордом административной панели
 */
class DashboardController extends Controller
{
    /**
     * Получение данных для дашборда
     * 
     * Возвращает основные данные для отображения на дашборде административной панели:
     * количество пользователей, постов, комментариев, а также базовую статистику.
     *
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "stats": {
     *             "users_count": 1250,
     *             "posts_count": 3568,
     *             "comments_count": 9845,
     *             "new_users_today": 25,
     *             "new_posts_today": 156
     *         },
     *         "active_users": [
     *             {
     *                 "id": 1,
     *                 "name": "Иван Петров",
     *                 "email": "ivan@example.com",
     *                 "activity_count": 53
     *             },
     *             {
     *                 "id": 2,
     *                 "name": "Мария Сидорова",
     *                 "email": "maria@example.com",
     *                 "activity_count": 48
     *             }
     *         ],
     *         "recent_posts": [
     *             {
     *                 "id": 156,
     *                 "title": "Новый пост",
     *                 "author": "Иван Петров",
     *                 "created_at": "2025-03-30T10:30:00Z"
     *             }
     *         ],
     *         "system_info": {
     *             "version": "1.5.2",
     *             "php_version": "8.2.0",
     *             "memory_usage": "256MB",
     *             "storage_usage": "75%"
     *         }
     *     }
     * }
     */
    public function index()
    {
        // Здесь должен быть код для получения данных дашборда
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'users_count' => 1250,
                    'posts_count' => 3568,
                    'comments_count' => 9845,
                    'new_users_today' => 25,
                    'new_posts_today' => 156
                ],
                'active_users' => [
                    [
                        'id' => 1,
                        'name' => 'Иван Петров',
                        'email' => 'ivan@example.com',
                        'activity_count' => 53
                    ],
                    [
                        'id' => 2,
                        'name' => 'Мария Сидорова',
                        'email' => 'maria@example.com',
                        'activity_count' => 48
                    ]
                ],
                'recent_posts' => [
                    [
                        'id' => 156,
                        'title' => 'Новый пост',
                        'author' => 'Иван Петров',
                        'created_at' => '2025-03-30T10:30:00Z'
                    ]
                ],
                'system_info' => [
                    'version' => '1.5.2',
                    'php_version' => '8.2.0',
                    'memory_usage' => '256MB',
                    'storage_usage' => '75%'
                ]
            ]
        ]);
    }
} 