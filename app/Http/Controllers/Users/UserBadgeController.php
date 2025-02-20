<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetActiveBadgeRequest;
use App\Http\Requests\StoreUserBadgeRequest;
use App\Http\Requests\UpdateUserBadgeRequest;
use App\Services\Users\UserBadgeService;
use Auth;
use Exception;
use Illuminate\Support\Facades\Cache;

class UserBadgeController extends Controller
{
    protected UserBadgeService $userBadgeService;

    public function __construct(UserBadgeService $userBadgeService)
    {
        $this->userBadgeService = $userBadgeService;
    }

    /**
     * Получить все badge пользователей.
     */
    public function index()
    {
        // Кешируем список наград
        $cacheKey = 'user_badges_all';

        $badges = Cache::get($cacheKey);

        if (! $badges) {
            $badges = $this->userBadgeService->getAllUserBadges();
            Cache::put($cacheKey, $badges, now()->addMinutes(10));
        }

        return response()->json($badges);
    }

    /**
     * Создать badge пользователя.
     */
    public function store(StoreUserBadgeRequest $request)
    {
        $userId = Auth::id();
        // Создаем новую награду
        $badge = $this->userBadgeService->createUserBadge($request->validated() + ['user_id' => $userId]);

        // Очищаем кеш, так как награды могли измениться
        Cache::forget('user_badges_all');

        return response()->json($badge, 201);
    }

    /**
     * Получить badge по ID.
     */
    public function show($id)
    {
        // Кешируем награду по ID
        $cacheKey = 'user_badge_'.$id;

        $badge = Cache::get($cacheKey);

        if (! $badge) {
            $badge = $this->userBadgeService->getUserBadgeById($id);

            Cache::put($cacheKey, $badge, now()->addMinutes(10));
        }

        return response()->json($badge);
    }

    /**
     * Обновить badge пользователя.
     */
    public function update(UpdateUserBadgeRequest $request, $id)
    {
        try {
            $badge = $this->userBadgeService->updateUserBadge($id, $request->validated());

            Cache::forget('user_badge_'.$id);
            Cache::forget('user_badges_all');

            return response()->json($badge);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }

    }

    public function setActiveBadge(SetActiveBadgeRequest $request)
    {
        $badgeId = $request->input('badge_id');

        try {
            $this->userBadgeService->setActiveBadge($badgeId);

            return response()->json(['message' => 'Badge set as active successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getActiveBadge()
    {
        try {
            $activeBadge = $this->userBadgeService->getActiveBadge();

            return response()->json($activeBadge, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Удалить badge пользователя.
     */
    public function destroy($id)
    {
        // Удаляем награду
        $this->userBadgeService->deleteUserBadge($id);

        // Очищаем кеш
        Cache::forget('user_badge_'.$id);
        Cache::forget('user_badges_all');

        return response()->json(['message' => 'Badge deleted successfully'], 200);
    }
}
