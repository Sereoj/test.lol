<?php

namespace App\Repositories;

use App\Models\Billing\Transaction;

class TransactionRepository
{
    /**
     * Создание новой транзакции
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Поиск транзакции по ID
     */
    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    /**
     * Поиск транзакции по внешнему ID
     */
    public function findByExternalId(string $externalId): ?Transaction
    {
        return Transaction::where('external_transaction_id', $externalId)->first();
    }

    /**
     * Обновление статуса транзакции
     */
    public function updateStatus(int $transactionId, string $status): bool
    {
        $transaction = $this->findById($transactionId);
        if (!$transaction) {
            return false;
        }

        $transaction->status = $status;
        return $transaction->save();
    }

    /**
     * Получение транзакций пользователя с пагинацией
     */
    public function findByUserIdWithPagination(int $userId, int $limit = 10, int $offset = 0)
    {
        $query = Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $transactions = $query->offset($offset)->limit($limit)->get();

        return [
            'data' => $transactions,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    /**
     * Удаление транзакции
     */
    public function delete(int $id): bool
    {
        $transaction = $this->findById($id);
        if (!$transaction) {
            return false;
        }

        return $transaction->delete();
    }
}
