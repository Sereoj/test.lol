<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserAchievementRequest;
use App\Models\Content\Achievement;
use App\Services\Content\AchievementService;
use Auth;
use Illuminate\Support\Facades\Cache;

class UserAchievementController extends Controller
{
    protected AchievementService $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Получить все достижения пользователя.
     */
    public function index()
    {
        $user = Auth::user();
        $cacheKey = 'user_achievements_'.$user->id;

        // Попытка получить достижения из кеша
        $achievements = Cache::get($cacheKey);

        // Если кеш пуст, извлекаем достижения и сохраняем в кеш
        if (! $achievements) {
            $achievements = $user->achievements;
            Cache::put($cacheKey, $achievements, now()->addMinutes(10)); // Кешируем на 10 минут
        }

        return response()->json($achievements);
    }

    /**
     * Добавить достижение пользователю.
     */
    public function store(StoreUserAchievementRequest $request)
    {
        $user = Auth::user();
        $achievement = Achievement::findOrFail($request->achievement_id);

        $this->achievementService->assignAchievementToUser($user, $achievement);

        // Очистка кеша достижений пользователя
        Cache::forget('user_achievements_'.$user->id);

        // Возвращаем актуальные достижения
        return response()->json($user->achievements, 201);
    }

    /**
     * Удалить достижение у пользователя.
     */
    public function destroy(Achievement $achievement)
    {
        $user = Auth::user();
        $this->achievementService->removeAchievementFromUser($user, $achievement);

        // Очистка кеша достижений пользователя
        Cache::forget('user_achievements_'.$user->id);

        return response()->json($user->achievements);
    }
}
