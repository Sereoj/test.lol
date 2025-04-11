<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Административная панель - Пользователи
 * @description Управление пользователями системы
 */
class UserController extends Controller
{
    /**
     * Получение списка пользователей
     * 
     * Возвращает пагинированный список пользователей с возможностью фильтрации и сортировки.
     *
     * @queryParam page integer Номер страницы. Example: 1
     * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
     * @queryParam search string Поиск по имени или email пользователя. Example: john
     * @queryParam sort string Поле для сортировки (id, name, email, created_at). Example: created_at
     * @queryParam order string Направление сортировки (asc, desc). Example: desc
     * @queryParam status string Фильтр по статусу (active, banned, unverified). Example: active
     * @queryParam role_id integer Фильтр по ID роли. Example: 2
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": [
     *         {
     *             "id": 1,
     *             "name": "Иван Петров",
     *             "email": "ivan@example.com",
     *             "status": "active",
     *             "role": "user",
     *             "created_at": "2025-01-15T10:00:00.000000Z",
     *             "last_login_at": "2025-03-30T08:45:12.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "name": "Мария Сидорова",
     *             "email": "maria@example.com",
     *             "status": "active",
     *             "role": "moderator",
     *             "created_at": "2025-01-25T14:30:00.000000Z",
     *             "last_login_at": "2025-03-29T16:20:45.000000Z"
     *         }
     *     ],
     *     "meta": {
     *         "current_page": 1,
     *         "from": 1,
     *         "last_page": 3,
     *         "path": "http://example.com/api/admin/users",
     *         "per_page": 15,
     *         "to": 15,
     *         "total": 40
     *     }
     * }
     */
    public function index(Request $request)
    {
        // Здесь должен быть код для получения списка пользователей
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Иван Петров',
                    'email' => 'ivan@example.com',
                    'status' => 'active',
                    'role' => 'user',
                    'created_at' => '2025-01-15T10:00:00.000000Z',
                    'last_login_at' => '2025-03-30T08:45:12.000000Z'
                ],
                [
                    'id' => 2,
                    'name' => 'Мария Сидорова',
                    'email' => 'maria@example.com',
                    'status' => 'active',
                    'role' => 'moderator',
                    'created_at' => '2025-01-25T14:30:00.000000Z',
                    'last_login_at' => '2025-03-29T16:20:45.000000Z'
                ]
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 3,
                'path' => 'http://example.com/api/admin/users',
                'per_page' => 15,
                'to' => 15,
                'total' => 40
            ]
        ]);
    }

    /**
     * Получение данных пользователя
     * 
     * Возвращает подробную информацию о пользователе.
     *
     * @urlParam id integer required ID пользователя. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный запрос" {
     *     "success": true,
     *     "data": {
     *         "id": 1,
     *         "name": "Иван Петров",
     *         "email": "ivan@example.com",
     *         "status": "active",
     *         "role": "user",
     *         "created_at": "2025-01-15T10:00:00.000000Z",
     *         "updated_at": "2025-03-15T12:30:00.000000Z",
     *         "last_login_at": "2025-03-30T08:45:12.000000Z",
     *         "last_login_ip": "192.168.1.1",
     *         "profile": {
     *             "avatar": "https://example.com/avatars/ivan.jpg",
     *             "bio": "Разработчик программного обеспечения",
     *             "phone": "+7 (999) 123-45-67",
     *             "location": "Москва, Россия"
     *         },
     *         "stats": {
     *             "posts_count": 35,
     *             "comments_count": 128,
     *             "likes_received": 450
     *         },
     *         "security": {
     *             "two_factor_enabled": true,
     *             "last_password_change": "2025-02-10T00:00:00.000000Z"
     *         }
     *     }
     * }
     * 
     * @response status=404 scenario="пользователь не найден" {
     *     "success": false,
     *     "message": "Пользователь не найден"
     * }
     */
    public function show($id)
    {
        // Здесь должен быть код для получения данных пользователя
        return response()->json([
            'success' => true,
            'data' => [
                'id' => 1,
                'name' => 'Иван Петров',
                'email' => 'ivan@example.com',
                'status' => 'active',
                'role' => 'user',
                'created_at' => '2025-01-15T10:00:00.000000Z',
                'updated_at' => '2025-03-15T12:30:00.000000Z',
                'last_login_at' => '2025-03-30T08:45:12.000000Z',
                'last_login_ip' => '192.168.1.1',
                'profile' => [
                    'avatar' => 'https://example.com/avatars/ivan.jpg',
                    'bio' => 'Разработчик программного обеспечения',
                    'phone' => '+7 (999) 123-45-67',
                    'location' => 'Москва, Россия'
                ],
                'stats' => [
                    'posts_count' => 35,
                    'comments_count' => 128,
                    'likes_received' => 450
                ],
                'security' => [
                    'two_factor_enabled' => true,
                    'last_password_change' => '2025-02-10T00:00:00.000000Z'
                ]
            ]
        ]);
    }

    /**
     * Создание нового пользователя
     * 
     * Создает нового пользователя с указанными данными.
     *
     * @bodyParam name string required Имя пользователя. Example: Иван Петров
     * @bodyParam email string required Email пользователя. Example: ivan@example.com
     * @bodyParam password string required Пароль пользователя (мин. 8 символов). Example: Secret123
     * @bodyParam role_id integer required ID роли пользователя. Example: 2
     * @bodyParam status string Статус пользователя (active, banned, unverified). Example: active
     * @bodyParam profile object Дополнительные данные профиля.
     * @bodyParam profile.avatar string URL аватара пользователя. Example: https://example.com/avatars/default.jpg
     * @bodyParam profile.bio string Информация о пользователе. Example: Разработчик программного обеспечения
     * @bodyParam profile.phone string Телефон пользователя. Example: +7 (999) 123-45-67
     * @bodyParam profile.location string Местоположение пользователя. Example: Москва, Россия
     * 
     * @authenticated
     * 
     * @response status=201 scenario="успешное создание" {
     *     "success": true,
     *     "message": "Пользователь успешно создан",
     *     "data": {
     *         "id": 41,
     *         "name": "Иван Петров",
     *         "email": "ivan@example.com",
     *         "status": "active",
     *         "role": "user",
     *         "created_at": "2025-03-30T11:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "email": ["Email уже используется"],
     *         "password": ["Пароль должен содержать не менее 8 символов"]
     *     }
     * }
     */
    public function store(Request $request)
    {
        // Здесь должен быть код для создания пользователя
        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно создан',
            'data' => [
                'id' => 41,
                'name' => 'Иван Петров',
                'email' => 'ivan@example.com',
                'status' => 'active',
                'role' => 'user',
                'created_at' => '2025-03-30T11:00:00.000000Z'
            ]
        ], 201);
    }

    /**
     * Обновление данных пользователя
     * 
     * Обновляет данные существующего пользователя.
     *
     * @urlParam id integer required ID пользователя. Example: 1
     * 
     * @bodyParam name string Имя пользователя. Example: Иван Сидоров
     * @bodyParam email string Email пользователя. Example: ivan.new@example.com
     * @bodyParam password string Новый пароль пользователя (мин. 8 символов). Example: NewSecret123
     * @bodyParam role_id integer ID роли пользователя. Example: 3
     * @bodyParam status string Статус пользователя (active, banned, unverified). Example: active
     * @bodyParam profile object Дополнительные данные профиля.
     * @bodyParam profile.avatar string URL аватара пользователя. Example: https://example.com/avatars/ivan.jpg
     * @bodyParam profile.bio string Информация о пользователе. Example: Старший разработчик
     * @bodyParam profile.phone string Телефон пользователя. Example: +7 (999) 123-45-67
     * @bodyParam profile.location string Местоположение пользователя. Example: Санкт-Петербург, Россия
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное обновление" {
     *     "success": true,
     *     "message": "Данные пользователя обновлены",
     *     "data": {
     *         "id": 1,
     *         "name": "Иван Сидоров",
     *         "email": "ivan.new@example.com",
     *         "status": "active",
     *         "role": "moderator",
     *         "updated_at": "2025-03-30T11:30:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="пользователь не найден" {
     *     "success": false,
     *     "message": "Пользователь не найден"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "email": ["Email уже используется"],
     *         "password": ["Пароль должен содержать не менее 8 символов"]
     *     }
     * }
     */
    public function update(Request $request, $id)
    {
        // Здесь должен быть код для обновления данных пользователя
        return response()->json([
            'success' => true,
            'message' => 'Данные пользователя обновлены',
            'data' => [
                'id' => 1,
                'name' => 'Иван Сидоров',
                'email' => 'ivan.new@example.com',
                'status' => 'active',
                'role' => 'moderator',
                'updated_at' => '2025-03-30T11:30:00.000000Z'
            ]
        ]);
    }

    /**
     * Удаление пользователя
     * 
     * Удаляет пользователя из системы.
     *
     * @urlParam id integer required ID пользователя. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешное удаление" {
     *     "success": true,
     *     "message": "Пользователь успешно удален"
     * }
     * 
     * @response status=404 scenario="пользователь не найден" {
     *     "success": false,
     *     "message": "Пользователь не найден"
     * }
     * 
     * @response status=403 scenario="недостаточно прав" {
     *     "success": false,
     *     "message": "Недостаточно прав для удаления этого пользователя"
     * }
     */
    public function destroy($id)
    {
        // Здесь должен быть код для удаления пользователя
        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно удален'
        ]);
    }

    /**
     * Бан пользователя
     * 
     * Блокирует пользователя в системе.
     *
     * @urlParam id integer required ID пользователя. Example: 1
     * 
     * @bodyParam reason string required Причина блокировки. Example: Нарушение правил сообщества
     * @bodyParam duration integer Продолжительность бана в днях (0 - постоянный). Example: 7
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная блокировка" {
     *     "success": true,
     *     "message": "Пользователь заблокирован",
     *     "data": {
     *         "id": 1,
     *         "status": "banned",
     *         "ban_reason": "Нарушение правил сообщества",
     *         "ban_expires_at": "2025-04-06T11:30:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="пользователь не найден" {
     *     "success": false,
     *     "message": "Пользователь не найден"
     * }
     * 
     * @response status=422 scenario="ошибка валидации" {
     *     "success": false,
     *     "message": "Ошибка валидации данных",
     *     "errors": {
     *         "reason": ["Необходимо указать причину блокировки"]
     *     }
     * }
     */
    public function ban(Request $request, $id)
    {
        // Здесь должен быть код для бана пользователя
        return response()->json([
            'success' => true,
            'message' => 'Пользователь заблокирован',
            'data' => [
                'id' => 1,
                'status' => 'banned',
                'ban_reason' => 'Нарушение правил сообщества',
                'ban_expires_at' => '2025-04-06T11:30:00.000000Z'
            ]
        ]);
    }

    /**
     * Разбан пользователя
     * 
     * Снимает блокировку с пользователя.
     *
     * @urlParam id integer required ID пользователя. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешный разбан" {
     *     "success": true,
     *     "message": "Блокировка снята",
     *     "data": {
     *         "id": 1,
     *         "status": "active"
     *     }
     * }
     * 
     * @response status=404 scenario="пользователь не найден" {
     *     "success": false,
     *     "message": "Пользователь не найден"
     * }
     * 
     * @response status=400 scenario="пользователь не заблокирован" {
     *     "success": false,
     *     "message": "Пользователь не заблокирован"
     * }
     */
    public function unban($id)
    {
        // Здесь должен быть код для разбана пользователя
        return response()->json([
            'success' => true,
            'message' => 'Блокировка снята',
            'data' => [
                'id' => 1,
                'status' => 'active'
            ]
        ]);
    }

    /**
     * Верификация пользователя
     * 
     * Подтверждает аккаунт пользователя (админом).
     *
     * @urlParam id integer required ID пользователя. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная верификация" {
     *     "success": true,
     *     "message": "Аккаунт пользователя подтвержден",
     *     "data": {
     *         "id": 1,
     *         "email_verified_at": "2025-03-30T12:00:00.000000Z"
     *     }
     * }
     * 
     * @response status=404 scenario="пользователь не найден" {
     *     "success": false,
     *     "message": "Пользователь не найден"
     * }
     * 
     * @response status=400 scenario="пользователь уже верифицирован" {
     *     "success": false,
     *     "message": "Аккаунт пользователя уже подтвержден"
     * }
     */
    public function verify($id)
    {
        // Здесь должен быть код для верификации пользователя
        return response()->json([
            'success' => true,
            'message' => 'Аккаунт пользователя подтвержден',
            'data' => [
                'id' => 1,
                'email_verified_at' => '2025-03-30T12:00:00.000000Z'
            ]
        ]);
    }

    /**
     * Отмена верификации пользователя
     * 
     * Отменяет подтверждение аккаунта пользователя.
     *
     * @urlParam id integer required ID пользователя. Example: 1
     * 
     * @authenticated
     * 
     * @response status=200 scenario="успешная отмена верификации" {
     *     "success": true,
     *     "message": "Подтверждение аккаунта отменено",
     *     "data": {
     *         "id": 1,
     *         "email_verified_at": null
     *     }
     * }
     * 
     * @response status=404 scenario="пользователь не найден" {
     *     "success": false,
     *     "message": "Пользователь не найден"
     * }
     */
    public function unverify($id)
    {
        // Здесь должен быть код для отмены верификации пользователя
        return response()->json([
            'success' => true,
            'message' => 'Подтверждение аккаунта отменено',
            'data' => [
                'id' => 1,
                'email_verified_at' => null
            ]
        ]);
    }
} 