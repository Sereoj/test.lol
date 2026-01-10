<?php

namespace App\Repositories;

use App\Models\Billing\MediaPurchase;

class MediaPurchaseRepository
{
    /**
     * Создание новой покупки медиа
     */
    public function create(array $data): MediaPurchase
    {
        return MediaPurchase::create($data);
    }

    /**
     * Поиск покупки по ID
     */
    public function findById(int $id): ?MediaPurchase
    {
        return MediaPurchase::find($id);
    }

    /**
     * Поиск покупки по media_id и user_id
     */
    public function findByMediaIdAndUserId(int $mediaId, int $userId): ?MediaPurchase
    {
        return MediaPurchase::where('media_id', $mediaId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Получение покупок медиа пользователя с пагинацией
     */
    public function findByUserIdWithPagination(int $userId, int $limit = 10, int $offset = 0)
    {
        $query = MediaPurchase::where('user_id', $userId)
            ->with(['media'])
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $purchases = $query->offset($offset)->limit($limit)->get();

        return [
            'data' => $purchases,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    /**
     * Проверка существования покупки медиа
     */
    public function exists(int $mediaId, int $userId): bool
    {
        return MediaPurchase::where('media_id', $mediaId)
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->exists();
    }
}
