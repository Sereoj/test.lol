<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Notifications\UserFollowedNotification;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Сервис для работы с подписками пользователей
 */
class UserFollowService extends BaseService
{
    /**
     * @var string
     */
    protected string $cachePrefix = 'user_follow';
    
    /**
     * Подписать пользователя на другого пользователя
     *
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, на которого подписываются
     * @return bool Результат операции
     */
    public function followUser(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) {
            $this->logWarning('User cannot follow themselves', ['user_id' => $followerId]);
            return false;
        }
        
        return $this->safeTransaction(function () use ($followerId, $followingId) {
            $follower = User::find($followerId);
            $following = User::find($followingId);

            if (!$follower || !$following) {
                $this->logWarning('User not found when following', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'follower_exists' => (bool) $follower,
                    'following_exists' => (bool) $following
                ]);
                return false;
            }

            if ($follower->following()->where('following_id', $followingId)->exists()) {
                $this->logInfo('User already follows this user', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return true;
            }

            $follower->following()->attach($followingId);
            $following->notify(new UserFollowedNotification($follower));

            $this->clearFollowCache($followerId, $followingId);

            $this->logInfo('User followed successfully', [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);

            return true;
        }, false, 'Follow user operation', [
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);
    }

    /**
     * Отписать пользователя от другого пользователя
     *
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, от которого отписываются
     * @return bool Результат операции
     */
    public function unfollowUser(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) {
            $this->logWarning('User cannot unfollow themselves', ['user_id' => $followerId]);
            return false;
        }
        
        return $this->safeTransaction(function () use ($followerId, $followingId) {
            $follower = User::find($followerId);
            $following = User::find($followingId);

            if (!$follower || !$following) {
                $this->logWarning('User not found when unfollowing', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'follower_exists' => (bool) $follower,
                    'following_exists' => (bool) $following
                ]);
                return false;
            }

            if (!$follower->following()->where('following_id', $followingId)->exists()) {
                $this->logInfo('User does not follow this user', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return false;
            }

            $follower->following()->detach($followingId);
            $this->clearFollowCache($followerId, $followingId);

            $this->logInfo('User unfollowed successfully', [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);

            return true;
        }, false, 'Unfollow user operation', [
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);
    }

    /**
     * Получить подписчиков пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowers(int $userId)
    {
        $cacheKey = $this->cachePrefix . '_followers_' . $userId;
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $user = User::find($userId);
            
            if (!$user) {
                $this->logWarning('User not found when getting followers', ['user_id' => $userId]);
                return collect();
            }
            
            $followers = $user->followers;
            
            $this->logInfo('Retrieved user followers', [
                'user_id' => $userId,
                'count' => $followers->count()
            ]);
            
            return $followers;
        });
    }

    /**
     * Получить подписки авторизованного пользователя
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowing()
    {
        $userId = Auth::id();
        if (!$userId) {
            $this->logWarning('No authenticated user when getting following');
            return collect();
        }
        
        return $this->getFollowingByUserId($userId);
    }
    
    /**
     * Получить подписки пользователя по ID
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowingByUserId(int $userId)
    {
        $cacheKey = $this->cachePrefix . '_following_' . $userId;
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $user = User::find($userId);
            
            if (!$user) {
                $this->logWarning('User not found when getting following', ['user_id' => $userId]);
                return collect();
            }
            
            $following = $user->following;
            
            $this->logInfo('Retrieved user following', [
                'user_id' => $userId,
                'count' => $following->count()
            ]);
            
            return $following;
        });
    }
    
    /**
     * Проверить, подписан ли пользователь на другого пользователя
     * 
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, на которого могут быть подписаны
     * @return bool true, если $followerId подписан на $followingId, иначе false
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) {
            $this->logInfo('User cannot follow themselves', ['user_id' => $followerId]);
            return false;
        }
        
        $cacheKey = $this->cachePrefix . '_is_following_' . $followerId . '_' . $followingId;
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($followerId, $followingId) {
            // Проверка существования подписчика
            $follower = User::find($followerId);
            if (!$follower) {
                $this->logWarning('Follower not found when checking isFollowing', [
                    'follower_id' => $followerId, 
                    'following_id' => $followingId
                ]);
                return false;
            }
            
            // Проверка существования пользователя, на которого подписываются
            $following = User::find($followingId);
            if (!$following) {
                $this->logWarning('Following user not found when checking isFollowing', [
                    'follower_id' => $followerId, 
                    'following_id' => $followingId
                ]);
                return false;
            }
            
            // Проверка наличия связи в таблице подписок
            $isFollowing = $follower->following()
                ->where('following_id', $followingId)
                ->exists();
            
            $this->logInfo('Checked if user is following another user', [
                'follower_id' => $followerId,
                'following_id' => $followingId,
                'is_following' => $isFollowing
            ]);
                
            return $isFollowing;
        });
    }
    
    /**
     * Очистить кеш связанный с подписками пользователя
     *
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, на которого подписываются
     * @return void
     */
    protected function clearFollowCache(int $followerId, int $followingId): void
    {
        $this->forgetCache([
            $this->cachePrefix . '_followers_' . $followingId,
            $this->cachePrefix . '_following_' . $followerId,
            $this->cachePrefix . '_is_following_' . $followerId . '_' . $followingId
        ]);
    }
}
