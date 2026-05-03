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
            Log::warning('Пользователь не может подписаться на самого себя', ['user_id' => $followerId]);
            return false;
        }

        try {
            $follower = User::find($followerId);
            $following = User::find($followingId);

            if (!$follower || !$following) {
                Log::warning('Пользователь не найден при подписке', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'follower_exists' => (bool) $follower,
                    'following_exists' => (bool) $following
                ]);
                return false;
            }

            if ($follower->following()->where('following_id', $followingId)->exists()) {
                Log::warning('Пользователь уже подписан на этого пользователя', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return true;
            }

            DB::transaction(function () use ($follower, $followingId) {
                $follower->following()->attach($followingId);
            });

            Log::info('Пользователь успешно подписан', [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);

            // Пытаемся отправить уведомление (не критично, если не получится)
            try {
                $following->notify(new UserFollowedNotification($follower));
            } catch (Exception $notificationException) {
                Log::warning('Не удалось отправить уведомление о подписке', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'error' => $notificationException->getMessage()
                ]);
            }

            return true;
        }catch (Exception $exception)
        {
            Log::error('Ошибка при подписке пользователя'. $exception->getMessage(), [
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
            Log::warning('Пользователь не может отписаться от самого себя', ['user_id' => $followerId]);
            return false;
        }

        try {
            $follower = User::find($followerId);
            $following = User::find($followingId);

            if (!$follower || !$following) {
                Log::warning('Пользователь не найден при отписке', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'follower_exists' => (bool) $follower,
                    'following_exists' => (bool) $following
                ]);
                return false;
            }

            if (!$follower->following()->where('following_id', $followingId)->exists()) {
                Log::warning('Пользователь не подписан на этого пользователя', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return false;
            }

            DB::transaction(function () use ($follower, $followingId) {
                $follower->following()->detach($followingId);
            });

            Log::info('Пользователь успешно отписан', [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);

            return true;
        }catch(Exception $exception){
            Log::error('Ошибка при отписке пользователя: ' . $exception->getMessage(), [
                'follower_id' => $followerId,
                'following_id' => $followingId,
            ]);
            throw $exception;
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
            Log::warning('Пользователь не найден при получении подписчиков', ['user_id' => $userId]);
            return collect();
        }

        $currentUserId = Auth::id();
        $followers = $user->followers;

        // Добавляем информацию о подписке текущего пользователя
        if ($currentUserId) {
            $followers->each(function ($follower) use ($currentUserId) {
                $follower->is_following = $follower->followers()
                    ->where('follower_id', $currentUserId)
                    ->exists();
            });
        }

        Log::info('Получены подписчики пользователя', [
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
            Log::warning('Нет авторизованного пользователя при получении подписок');
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
            Log::warning('Пользователь не найден при получении подписок', ['user_id' => $userId]);
            return collect();
        }

        $currentUserId = Auth::id();
        $following = $user->following;

        // Добавляем информацию о подписке текущего пользователя
        if ($currentUserId) {
            $following->each(function ($followingUser) use ($currentUserId) {
                $followingUser->is_following = $followingUser->followers()
                    ->where('follower_id', $currentUserId)
                    ->exists();
            });
        }

        Log::info('Получены подписки пользователя', [
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
            Log::warning('Пользователь не может подписаться на самого себя', ['user_id' => $followerId]);
            return false;
        }
            $follower = User::find($followerId);
            if (!$follower) {
                Log::warning('Подписчик не найден при проверке подписки', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return false;
            }

            // Проверка существования пользователя, на которого подписываются
            $following = User::find($followingId);
            if (!$following) {
                Log::warning('Пользователь, на которого подписываются, не найден при проверке подписки', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);
                return false;
            }

            // Проверка наличия связи в таблице подписок
            $isFollowing = $follower->following()
                ->where('following_id', $followingId)
                ->exists();

            Log::info('Проверено, подписан ли пользователь на другого пользователя', [
                'follower_id' => $followerId,
                'following_id' => $followingId,
                'is_following' => $isFollowing
            ]);

            return $isFollowing;
    }

    /**
     * Проверить, являются ли два пользователя взаимными друзьями
     *
     * @param int $userId1 ID первого пользователя
     * @param int $userId2 ID второго пользователя
     * @return bool true, если взаимные друзья, иначе false
     */
    public function areMutualFriends(int $userId1, int $userId2): bool
    {
        if ($userId1 === $userId2) {
            return false;
        }

        return $this->isFollowing($userId1, $userId2)
            && $this->isFollowing($userId2, $userId1);
    }
}
