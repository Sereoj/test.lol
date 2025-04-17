<?php

namespace App\Services\Messaging;

use App\Models\Messaging\Notification;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getAll(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->notificationRepository->getAll($filters);
    }

    public function create(array $data): Notification
    {
        $notification = $this->notificationRepository->create($data);
        Log::info('Notification created successfully', ['notification_id' => $notification->id]);
        return $notification;
    }

    public function getById(int $id): ?Notification
    {
        return $this->notificationRepository->findById($id);
    }

    public function updateNotification(int $id, array $data): bool
    {
        $notification = $this->getById($id);
        return $notification ? $this->notificationRepository->update($notification, $data) : false;
    }

    public function deleteNotification(Notification $notification): void
    {
        $this->notificationRepository->delete($notification);
        Log::info('Notification deleted successfully', ['notification_id' => $notification->id]);
    }

    public function getNotificationsByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->notificationRepository->getNotificationsByUserId($userId);
    }

    public function markAsRead(int $notificationId): ?Notification
    {
        $notification = $this->getById($notificationId);
        if ($notification) {
            $notification->update(['read_at' => now()]);
            Log::info('Notification marked as read', ['notification_id' => $notificationId]);
        }
        return $notification;
    }
} 