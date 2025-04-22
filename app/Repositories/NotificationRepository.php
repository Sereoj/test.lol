<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationRepository
{
    protected Notification $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    /**
     * Получить уведомления пользователя
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserNotifications(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить непрочитанные уведомления пользователя
     *
     * @param int $userId
     * @return Collection
     */
    public function getUnreadNotifications(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Найти уведомление по ID
     *
     * @param int $id
     * @return Notification|null
     */
    public function findById(int $id): ?Notification
    {
        return $this->model->find($id);
    }

    /**
     * Отметить уведомление как прочитанное
     *
     * @param int $id
     * @return bool
     */
    public function markAsRead(int $id): bool
    {
        return $this->model
            ->where('id', $id)
            ->update(['read_at' => now()]);
    }

    /**
     * Отметить все уведомления пользователя как прочитанные
     *
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Удалить уведомление
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    /**
     * Создать новое уведомление
     *
     * @param array $data
     * @return Notification
     */
    public function create(array $data): Notification
    {
        return $this->model->create($data);
    }
} 