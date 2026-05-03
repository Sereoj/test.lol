<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserFollowerResource;
use App\Services\Users\UserFollowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

// Контроллер для работы с подписками на пользователей
class UserFollowController extends Controller
{
    protected UserFollowService $followService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_FOLLOWING = 'user_%s_following_%s';
    private const CACHE_KEY_USER_FOLLOWERS = 'user_%s_followers';
    private const CACHE_KEY_USER_FOLLOWING_LIST = 'user_%s_following';

    public function __construct(UserFollowService $followService)
    {
        $this->followService = $followService;
    }

    /**
     * Подписка на пользователя.
     */
    public function follow(Request $request, $userId)
    {
        try {
            $followerId = Auth::id();

            // Проверка, не подписан ли пользователь уже
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWING, $followerId, $userId);
            $isFollowing = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($followerId, $userId) {
                return $this->followService->isFollowing($followerId, $userId);
            });

            if ($isFollowing) {
                Log::info('Пользователь уже подписан', ['follower_id' => $followerId, 'user_id' => $userId]);
                return $this->successResponse([
                    'status' => 'already_subscribed',
                    'message' => 'You are already following this user'
                ]);
            }

            $result = $this->followService->followUser($followerId, $userId);

            if ($result) {
                // Обновляем кеш
                $this->forgetCache([
                    sprintf(self::CACHE_KEY_USER_FOLLOWERS, $userId),
                    sprintf(self::CACHE_KEY_USER_FOLLOWING_LIST, $followerId)
                ]);

                // Устанавливаем кеш подписки
                $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () {
                    return true;
                }, true);

                Log::info('Пользователь успешно подписан', ['follower_id' => $followerId, 'user_id' => $userId]);

                return $this->successResponse([
                    'status' => 'subscribed',
                    'message' => 'User followed successfully'
                ]);
            }

            Log::warning('Пользователь не найден', ['follower_id' => $followerId, 'user_id' => $userId]);
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            Log::error('Ошибка при подписке на пользователя: ' . $e->getMessage(), [
                'follower_id' => Auth::id(),
                'user_id' => $userId
            ]);
            return $this->errorResponse('An error occurred while following user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Отписка от пользователя.
     */
    public function unfollow(Request $request, $userId)
    {
        try {
            $followerId = Auth::id();

            // Проверка, не отписан ли пользователь уже
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWING, $followerId, $userId);
            $isFollowing = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($followerId, $userId) {
                return $this->followService->isFollowing($followerId, $userId);
            });

            if (!$isFollowing) {
                Log::info('Пользователь не подписан', ['follower_id' => $followerId, 'user_id' => $userId]);
                return $this->successResponse([
                    'status' => 'not_following',
                    'message' => 'You are not following this user'
                ]);
            }

            $result = $this->followService->unfollowUser($followerId, $userId);

            if ($result) {
                // Очищаем кеш
                $this->forgetCache([
                    $cacheKey,
                    sprintf(self::CACHE_KEY_USER_FOLLOWERS, $userId),
                    sprintf(self::CACHE_KEY_USER_FOLLOWING_LIST, $followerId)
                ]);

                Log::info('Пользователь успешно отписан', ['follower_id' => $followerId, 'user_id' => $userId]);

                return $this->successResponse([
                    'status' => 'unsubscribed',
                    'message' => 'User unfollowed successfully'
                ]);
            }

            Log::warning('Пользователь не найден', ['follower_id' => $followerId, 'user_id' => $userId]);
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            Log::error('Ошибка при отписке от пользователя: ' . $e->getMessage(), [
                'follower_id' => Auth::id(),
                'user_id' => $userId
            ]);
            return $this->errorResponse('An error occurred while unfollowing user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Получить список подписчиков пользователя.
     */
    public function followers($userId)
    {
        try {
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWERS, $userId);

            $followers = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return $this->followService->getFollowers($userId);
            });

            Log::info('Подписчики успешно получены', ['user_id' => $userId]);

            return $this->successResponse(UserFollowerResource::collection($followers));
        } catch (Exception $e) {
            Log::error('Ошибка при получении подписчиков: ' . $e->getMessage(), ['user_id' => $userId]);
            return $this->errorResponse('An error occurred while retrieving followers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Получить список пользователей, на которых подписан данный пользователь.
     */
    public function following($userId)
    {
        try {
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWING_LIST, $userId);

            $following = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return $this->followService->getFollowingByUserId($userId);
            });

            Log::info('Список подписок успешно получен', ['user_id' => $userId]);

            return $this->successResponse(UserFollowerResource::collection($following));
        } catch (Exception $e) {
            Log::error('Ошибка при получении списка подписок: ' . $e->getMessage(), ['user_id' => $userId]);
            return $this->errorResponse('An error occurred while retrieving following list: ' . $e->getMessage(), 500);
        }
    }
}
