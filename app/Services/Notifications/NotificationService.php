<?php

namespace App\Services\Notifications;

use App\Models\Users\User;
use App\Notifications\SystemNotification;
use App\Services\Base\SimpleService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Сервис для работы с уведомлениями
 */
class NotificationService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'notifications';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 5;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('NotificationService');
    }

    /**
     * Получить уведомления пользователя
     *
     * @param User|null $user
     * @param bool $onlyUnread
     * @param int $limit
     * @return Collection
     */
    public function getUserNotifications(?User $user = null, bool $onlyUnread = false, int $limit = 20): Collection
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            $this->logWarning('Попытка получить уведомления без авторизованного пользователя');
            return collect();
        }

        $cacheKey = $this->buildCacheKey('user_notifications', [$user->id, $onlyUnread, $limit]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user, $onlyUnread, $limit) {
            $this->logInfo("Получение уведомлений для пользователя ID: {$user->id}");

            $query = $user->notifications();

            if ($onlyUnread) {
                $query->whereNull('read_at');
            }

            $notifications = $query->limit($limit)->get();

            $this->logInfo("Получено {$notifications->count()} уведомлений для пользователя ID: {$user->id}");

            return $notifications;
        });
    }

    /**
     * Пометить уведомление как прочитанное
     *
     * @param string $id
     * @param User|null $user
     * @return bool
     */
    public function markAsRead(string $id, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            $this->logWarning('Попытка пометить уведомление как прочитанное без авторизованного пользователя');
            return false;
        }

        $this->logInfo("Пометка уведомления ID: {$id} как прочитанного для пользователя ID: {$user->id}");

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            $this->logWarning("Уведомление ID: {$id} не найдено для пользователя ID: {$user->id}");
            return false;
        }

        $notification->markAsRead();

        $this->forgetCache($this->buildCacheKey('user_notifications', [$user->id]));
        $this->forgetCache($this->buildCacheKey('unread_count', [$user->id]));

        return true;
    }

    /**
     * Пометить все уведомления пользователя как прочитанные
     *
     * @param User|null $user
     * @return bool
     */
    public function markAllAsRead(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            $this->logWarning('Попытка пометить все уведомления как прочитанные без авторизованного пользователя');
            return false;
        }

        $this->logInfo("Пометка всех уведомлений как прочитанных для пользователя ID: {$user->id}");

        $user->unreadNotifications->markAsRead();

        $this->forgetCache($this->buildCacheKey('user_notifications', [$user->id]));
        $this->forgetCache($this->buildCacheKey('unread_count', [$user->id]));

        return true;
    }

    /**
     * Удалить уведомление
     *
     * @param string $id
     * @param User|null $user
     * @return bool
     */
    public function deleteNotification(string $id, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            $this->logWarning('Попытка удалить уведомление без авторизованного пользователя');
            return false;
        }

        $this->logInfo("Удаление уведомления ID: {$id} для пользователя ID: {$user->id}");

        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            $this->logWarning("Уведомление ID: {$id} не найдено для пользователя ID: {$user->id}");
            return false;
        }

        $result = $notification->delete();

        if ($result) {
            $this->forgetCache($this->buildCacheKey('user_notifications', [$user->id]));
            $this->forgetCache($this->buildCacheKey('unread_count', [$user->id]));
        }

        return $result;
    }

    /**
     * Получить количество непрочитанных уведомлений
     *
     * @param User|null $user
     * @return int
     */
    public function getUnreadCount(?User $user = null): int
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return 0;
        }

        $cacheKey = $this->buildCacheKey('unread_count', [$user->id]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user) {
            return $user->unreadNotifications()->count();
        });
    }

    /**
     * Отправить уведомление пользователю
     *
     * @param User $user
     * @param Notification $notification
     * @return void
     */
    public function sendNotification(User $user, Notification $notification): void
    {
        $this->logInfo("Отправка уведомления типа " . get_class($notification) . " пользователю ID: {$user->id}");

        $user->notify($notification);

        $this->forgetCache($this->buildCacheKey('user_notifications', [$user->id]));
        $this->forgetCache($this->buildCacheKey('unread_count', [$user->id]));
    }

    /**
     * Отправить системное уведомление
     *
     * @param User $user
     * @param string $message
     * @param string $type
     * @param array $data
     * @return void
     */
    public function sendSystemNotification(User $user, string $message, string $type = 'info', array $data = []): void
    {
        $this->logInfo("Отправка системного уведомления типа '{$type}' пользователю ID: {$user->id}");

        $notification = new SystemNotification($message, $type, $data);
        $this->sendNotification($user, $notification);
    }

    /**
     * Очистить кеш уведомлений для пользователя
     *
     * @param int $userId
     * @return bool
     */
    public function clearNotificationCache(int $userId): bool
    {
        $this->logInfo("Очистка кеша уведомлений для пользователя ID: {$userId}");

        return $this->forgetCache([
            $this->buildCacheKey('user_notifications', [$userId]),
            $this->buildCacheKey('unread_count', [$userId])
        ]);
    }
}
