<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Notifications\UserFollowedNotification;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * Сервис для работы с подписками пользователей
 */
class UserFollowService
{
    /**
     * Подписывает одного пользователя на другого.
     *
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, на которого подписываются
     * @return bool Возвращает true, если подписка успешна, иначе false
     * @throws Exception Если возникает ошибка при выполнении операции
     */
    public function followUser(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) {
            Log::warning('User cannot follow themselves', ['user_id' => $followerId]);
            return false;
        }

        try {
            DB::beginTransaction();
            $follower = User::find($followerId);
            $following = User::find($followingId);

            if (!$follower || !$following) {
                Log::warning('User not found when following', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'follower_exists' => (bool) $follower,
                    'following_exists' => (bool) $following
                ]);
                return false;
            }

            if ($follower->following()->where('following_id', $followingId)->exists()) {
                Log::warning('User already follows this user', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return true;
            }

            $follower->following()->attach($followingId);
            $following->notify(new UserFollowedNotification($follower));

            Log::info('User followed successfully', [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);

            return true;
        }catch (Exception $exception)
        {
            DB::rollBack();
            Log::error('Error user following'. $exception->getMessage(), [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);
            throw $exception;
        }
    }

    /**
     * Отписывает одного пользователя от другого.
     *
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, от которого отписываются
     * @return bool Возвращает true, если отписка успешна, иначе false
     * @throws Exception Если возникает ошибка при выполнении операции
     */
    public function unfollowUser(int $followerId, int $followingId)
    {
        if ($followerId === $followingId) {
            Log::warning('User cannot unfollow themselves', ['user_id' => $followerId]);
            return false;
        }

        try {
            DB::beginTransaction();
            $follower = User::find($followerId);
            $following = User::find($followingId);

            if (!$follower || !$following) {
                Log::warning('User not found when unfollowing', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'follower_exists' => (bool) $follower,
                    'following_exists' => (bool) $following
                ]);
                return false;
            }

            if (!$follower->following()->where('following_id', $followingId)->exists()) {
                Log::warning('User does not follow this user', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return false;
            }

            $follower->following()->detach($followingId);

            Log::info('User unfollowed successfully', [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);

            return true;
        }catch(Exception $exception){
            DB::rollBack();
            Log::error('Error user unfollowing: ' . $exception->getMessage(), [
                'follower_id' => $followerId,
                'following_id' => $followingId,
            ]);
        }
    }

    /**
     * Получить подписчиков пользователя
     *
     * @param int $userId ID пользователя
     */
    public function getFollowers(int $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            Log::warning('User not found when getting followers', ['user_id' => $userId]);
            return collect();
        }

        $followers = $user->followers;

        Log::info('Retrieved user followers', [
            'user_id' => $userId,
            'count' => $followers->count()
        ]);

        return $followers;
    }

    /**
     * Получить подписки авторизованного пользователя
     *
     */
    public function getFollowing()
    {
        $userId = Auth::id();
        if (!$userId) {
            Log::warning('No authenticated user when getting following');
            return collect();
        }

        return $this->getFollowingByUserId($userId);
    }

    /**
     * Получить подписки пользователя по ID
     *
     * @param int $userId ID пользователя
     */
    public function getFollowingByUserId(int $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            Log::warning('User not found when getting following', ['user_id' => $userId]);
            return collect();
        }

        $following = $user->following;

        Log::info('Retrieved user following', [
            'user_id' => $userId,
            'count' => $following->count()
        ]);

        return $following;
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
            Log::warning('User cannot follow themselves', ['user_id' => $followerId]);
            return false;
        }
            $follower = User::find($followerId);
            if (!$follower) {
                Log::warning('Follower not found when checking isFollowing', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return false;
            }

            // Проверка существования пользователя, на которого подписываются
            $following = User::find($followingId);
            if (!$following) {
                Log::warning('Following user not found when checking isFollowing', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return false;
            }

            // Проверка наличия связи в таблице подписок
            $isFollowing = $follower->following()
                ->where('following_id', $followingId)
                ->exists();

            Log::info('Checked if user is following another user', [
                'follower_id' => $followerId,
                'following_id' => $followingId,
                'is_following' => $isFollowing
            ]);

            return $isFollowing;
    }
}
