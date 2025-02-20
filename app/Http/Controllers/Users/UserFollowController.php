<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UserFollowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserFollowController extends Controller
{
    protected UserFollowService $followService;

    public function __construct(UserFollowService $followService)
    {
        $this->followService = $followService;
    }

    /**
     * Подписка на пользователя.
     */
    public function follow(Request $request, $userId)
    {
        $followerId = Auth::id();

        // Проверка, не подписан ли пользователь уже
        $cacheKey = "user_{$followerId}_following_{$userId}";
        $isFollowing = Cache::get($cacheKey);

        if ($isFollowing) {
            return response()->json(['message' => 'You are already following this user']);
        }

        $result = $this->followService->followUser($followerId, $userId);

        if ($result) {
            // Кешируем подписку
            Cache::put($cacheKey, true, now()->addMinutes(10));

            return response()->json(['message' => 'User followed successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    /**
     * Отписка от пользователя.
     */
    public function unfollow(Request $request, $userId)
    {
        $followerId = Auth::id();

        // Проверка, не отписан ли пользователь уже
        $cacheKey = "user_{$followerId}_following_{$userId}";
        $isFollowing = Cache::get($cacheKey);

        if (! $isFollowing) {
            return response()->json(['message' => 'You are not following this user']);
        }

        $result = $this->followService->unfollowUser($followerId, $userId);

        if ($result) {
            // Убираем кеш подписки
            Cache::forget($cacheKey);

            return response()->json(['message' => 'User unfollowed successfully']);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    /**
     * Получить список подписчиков пользователя.
     */
    public function followers()
    {
        // Кешируем список подписчиков
        $userId = Auth::id();
        $cacheKey = "user_{$userId}_followers";
        $followers = Cache::get($cacheKey);

        if (! $followers) {
            $followers = $this->followService->getFollowers($userId);
            Cache::put($cacheKey, $followers, now()->addMinutes(10));
        }

        return response()->json($followers);
    }

    /**
     * Получить список пользователей, на которых подписан данный пользователь.
     */
    public function following()
    {
        // Кешируем список подписок

        $userId = Auth::id();

        $cacheKey = "user_{$userId}_following";
        $following = Cache::get($cacheKey);

        if (! $following) {
            $following = $this->followService->getFollowing();

            // Кешируем на 10 минут
            Cache::put($cacheKey, $following, now()->addMinutes(10));
        }

        return response()->json($following);
    }
}
