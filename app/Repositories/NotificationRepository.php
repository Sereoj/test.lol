<?php

namespace App\Repositories;

use App\Models\Messaging\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository
{
    protected Notification $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->query();

        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, $value);
                }
            }
        }

        return $query->get();
    }

    public function create(array $data): Notification
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?Notification
    {
        return $this->model->find($id);
    }

    public function update(Notification $notification, array $data): bool
    {
        return $notification->update($data);
    }

    public function delete(Notification $notification): ?bool
    {
        return $notification->delete();
    }

    public function getNotificationsByUserId(int $userId): Collection
    {
        return $this->model->where('notifiable_type', \App\Models\Users\User::class)
            ->where('notifiable_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
} 