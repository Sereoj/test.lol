<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserBadgeRequest;
use App\Http\Requests\UpdateUserBadgeRequest;
use App\Services\UserBadgeService;
use Illuminate\Support\Facades\Cache;

class UserBadgeController extends Controller
{
    protected UserBadgeService $userBadgeService;

    public function __construct(UserBadgeService $userBadgeService)
    {
        $this->userBadgeService = $userBadgeService;
    }

    /**
     * Получить все награды пользователей.
     */
    public function index()
    {
        // Кешируем список наград
        $cacheKey = 'user_badges_all';

        // Проверяем, есть ли данные в кеше
        $badges = Cache::get($cacheKey);

        if (!$badges) {
            $badges = $this->userBadgeService->getAllUserBadges();

            // Сохраняем данные в кеш на 10 минут
            Cache::put($cacheKey, $badges, now()->addMinutes(10));
        }

        return response()->json($badges);
    }

    /**
     * Создать награду пользователя.
     */
    public function store(StoreUserBadgeRequest $request)
    {
        // Создаем новую награду
        $badge = $this->userBadgeService->createUserBadge($request->validated());

        // Очищаем кеш, так как награды могли измениться
        Cache::forget('user_badges_all');

        return response()->json($badge, 201);
    }

    /**
     * Получить награду по ID.
     */
    public function show($id)
    {
        // Кешируем награду по ID
        $cacheKey = 'user_badge_' . $id;

        $badge = Cache::get($cacheKey);

        if (!$badge) {
            $badge = $this->userBadgeService->getUserBadgeById($id);

            // Сохраняем награду в кеш
            Cache::put($cacheKey, $badge, now()->addMinutes(10));
        }

        return response()->json($badge);
    }

    /**
     * Обновить награду пользователя.
     */
    public function update(UpdateUserBadgeRequest $request, $id)
    {
        // Обновляем награду
        $badge = $this->userBadgeService->updateUserBadge($id, $request->validated());

        // Очищаем кеш награды
        Cache::forget('user_badge_' . $id);

        // Очищаем общий кеш наград
        Cache::forget('user_badges_all');

        return response()->json($badge);
    }

    /**
     * Удалить награду пользователя.
     */
    public function destroy($id)
    {
        // Удаляем награду
        $this->userBadgeService->deleteUserBadge($id);

        // Очищаем кеш
        Cache::forget('user_badge_' . $id);
        Cache::forget('user_badges_all');

        return response()->json(['message' => 'Badge deleted successfully'], 200);
    }
}
