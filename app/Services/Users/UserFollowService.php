<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Notifications\UserFollowedNotification;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Сервис для работы с подписками пользователей
 */
class UserFollowService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'user_follow';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('UserFollowService');
    }

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
            $this->logWarning('Пользователь не может подписаться на самого себя', ['user_id' => $followerId]);
            return false;
        }

        return $this->transaction(function () use ($followerId, $followingId) {
            try {
                $follower = User::find($followerId);
                $following = User::find($followingId);

                if (!$follower || !$following) {
                    $this->logWarning('Пользователь не найден при попытке подписки', [
                        'follower_id' => $followerId,
                        'following_id' => $followingId,
                        'follower_exists' => (bool) $follower,
                        'following_exists' => (bool) $following
                    ]);
                    return false;
                }

                if ($follower->following()->where('following_id', $followingId)->exists()) {
                    $this->logInfo('Пользователь уже подписан', [
                        'follower_id' => $followerId,
                        'following_id' => $followingId
                    ]);
                    return true;
                }

                $follower->following()->attach($followingId);
                $following->notify(new UserFollowedNotification($follower));

                $this->clearFollowCache($followerId, $followingId);

                $this->logInfo('Пользователь успешно подписался', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);

                return true;
            } catch (Exception $e) {
                $this->logError('Ошибка при подписке на пользователя', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ], $e);

                return false;
            }
        });
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
            $this->logWarning('Пользователь не может отписаться от самого себя', ['user_id' => $followerId]);
            return false;
        }

        return $this->transaction(function () use ($followerId, $followingId) {
            try {
                $follower = User::find($followerId);
                $following = User::find($followingId);

                if (!$follower || !$following) {
                    $this->logWarning('Пользователь не найден при попытке отписки', [
                        'follower_id' => $followerId,
                        'following_id' => $followingId,
                        'follower_exists' => (bool) $follower,
                        'following_exists' => (bool) $following
                    ]);
                    return false;
                }

                if (!$follower->following()->where('following_id', $followingId)->exists()) {
                    $this->logInfo('Пользователь не подписан', [
                        'follower_id' => $followerId,
                        'following_id' => $followingId
                    ]);
                    return false;
                }

                $follower->following()->detach($followingId);
                $this->clearFollowCache($followerId, $followingId);

                $this->logInfo('Пользователь успешно отписался', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ]);

                return true;
            } catch (Exception $e) {
                $this->logError('Ошибка при отписке от пользователя', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ], $e);

                return false;
            }
        });
    }

    /**
     * Получить подписчиков пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowers(int $userId)
    {
        $cacheKey = $this->buildCacheKey('followers', [$userId]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение подписчиков пользователя', ['user_id' => $userId]);

            try {
                $user = User::find($userId);

                if (!$user) {
                    $this->logWarning('Пользователь не найден при получении подписчиков', ['user_id' => $userId]);
                    return collect();
                }

                $followers = $user->followers;

                $this->logInfo('Получены подписчики пользователя', [
                    'user_id' => $userId,
                    'count' => $followers->count()
                ]);

                return $followers;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении подписчиков', ['user_id' => $userId], $e);
                return collect();
            }
        });
    }

    /**
     * Получить подписки текущего пользователя
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFollowing()
    {
        $user = Auth::user();

        if (!$user) {
            $this->logWarning('Попытка получить подписки без авторизации');
            return collect();
        }

        return $this->getFollowingByUserId($user->id);
    }

    /**
     * Получить подписки пользователя по ID
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowingByUserId(int $userId)
    {
        $cacheKey = $this->buildCacheKey('following', [$userId]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение подписок пользователя', ['user_id' => $userId]);

            try {
                $user = User::find($userId);

                if (!$user) {
                    $this->logWarning('Пользователь не найден при получении подписок', ['user_id' => $userId]);
                    return collect();
                }

                $following = $user->following;

                $this->logInfo('Получены подписки пользователя', [
                    'user_id' => $userId,
                    'count' => $following->count()
                ]);

                return $following;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении подписок', ['user_id' => $userId], $e);
                return collect();
            }
        });
    }

    /**
     * Проверить, подписан ли один пользователь на другого
     *
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, на которого может быть подписка
     * @return bool
     */
    public function isFollowing(int $followerId, int $followingId): bool
    {
        if ($followerId === $followingId) {
            return false;
        }

        $cacheKey = $this->buildCacheKey('is_following', [$followerId, $followingId]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($followerId, $followingId) {
            $this->logInfo('Проверка подписки', [
                'follower_id' => $followerId,
                'following_id' => $followingId
            ]);

            try {
                $follower = User::find($followerId);

                if (!$follower) {
                    $this->logWarning('Пользователь не найден при проверке подписки', ['user_id' => $followerId]);
                    return false;
                }

                $isFollowing = $follower->following()
                    ->where('following_id', $followingId)
                    ->exists();

                $this->logInfo('Результат проверки подписки', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId,
                    'is_following' => $isFollowing
                ]);

                return $isFollowing;
            } catch (Exception $e) {
                $this->logError('Ошибка при проверке подписки', [
                    'follower_id' => $followerId,
                    'following_id' => $followingId
                ], $e);

                return false;
            }
        });
    }

    /**
     * Очистить кеш подписок
     *
     * @param int $followerId ID подписчика
     * @param int $followingId ID пользователя, на которого подписаны
     */
    protected function clearFollowCache(int $followerId, int $followingId): void
    {
        $this->logInfo('Очистка кеша подписок', [
            'follower_id' => $followerId,
            'following_id' => $followingId
        ]);

        // Очистка кеша подписчиков
        $this->forgetCache($this->buildCacheKey('followers', [$followingId]));

        // Очистка кеша подписок
        $this->forgetCache($this->buildCacheKey('following', [$followerId]));

        // Очистка кеша проверки подписки
        $this->forgetCache($this->buildCacheKey('is_following', [$followerId, $followingId]));
    }
}
