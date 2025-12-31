<?php

namespace App\Repositories;

use App\Models\Billing\Purchase;

class PurchaseRepository
{
    /**
     * Создание новой покупки
     */
    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    /**
     * Поиск покупки по ID
     */
    public function findById(int $id): ?Purchase
    {
        return Purchase::find($id);
    }

    /**
     * Поиск покупки по post_id и user_id
     */
    public function findByPostIdAndUserId(int $postId, int $userId): ?Purchase
    {
        return Purchase::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Получение покупок пользователя с пагинацией
     */
    public function findByUserIdWithPagination(int $userId, int $limit = 10, int $offset = 0)
    {
        $query = Purchase::where('user_id', $userId)
            ->with(['post'])
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
}
