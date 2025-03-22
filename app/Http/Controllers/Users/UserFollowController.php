<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UserFollowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

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
                Log::info('User already following', ['follower_id' => $followerId, 'user_id' => $userId]);
                return $this->successResponse(['message' => 'You are already following this user']);
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
                
                Log::info('User followed successfully', ['follower_id' => $followerId, 'user_id' => $userId]);

                return $this->successResponse(['message' => 'User followed successfully']);
            }
            
            Log::warning('User not found', ['follower_id' => $followerId, 'user_id' => $userId]);
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            Log::error('Error following user: ' . $e->getMessage(), [
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
                Log::info('User not following', ['follower_id' => $followerId, 'user_id' => $userId]);
                return $this->successResponse(['message' => 'You are not following this user']);
            }

            $result = $this->followService->unfollowUser($followerId, $userId);

            if ($result) {
                // Очищаем кеш
                $this->forgetCache([
                    $cacheKey,
                    sprintf(self::CACHE_KEY_USER_FOLLOWERS, $userId),
                    sprintf(self::CACHE_KEY_USER_FOLLOWING_LIST, $followerId)
                ]);
                
                Log::info('User unfollowed successfully', ['follower_id' => $followerId, 'user_id' => $userId]);

                return $this->successResponse(['message' => 'User unfollowed successfully']);
            }
            
            Log::warning('User not found', ['follower_id' => $followerId, 'user_id' => $userId]);
            return $this->errorResponse('User not found', 404);
        } catch (Exception $e) {
            Log::error('Error unfollowing user: ' . $e->getMessage(), [
                'follower_id' => Auth::id(),
                'user_id' => $userId
            ]);
            return $this->errorResponse('An error occurred while unfollowing user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Получить список подписчиков пользователя.
     */
    public function followers()
    {
        try {
            $userId = Auth::id();
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWERS, $userId);
            
            $followers = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return $this->followService->getFollowers($userId);
            });
            
            Log::info('Followers retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($followers);
        } catch (Exception $e) {
            Log::error('Error retrieving followers: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while retrieving followers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Получить список пользователей, на которых подписан данный пользователь.
     */
    public function following()
    {
        try {
            $userId = Auth::id();
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWING_LIST, $userId);
            
            $following = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () {
                return $this->followService->getFollowing();
            });
            
            Log::info('Following list retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($following);
        } catch (Exception $e) {
            Log::error('Error retrieving following list: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while retrieving following list: ' . $e->getMessage(), 500);
        }
    }
}
