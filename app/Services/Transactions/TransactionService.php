<?php

namespace App\Services\Transactions;

use App\Models\Transaction;
use App\Models\Users\User;
use App\Notifications\TransactionNotification;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    // Метод для получения транзакций пользователя
    public function getUserTransactions(int $userId)
    {
        return Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Для обработки других типов транзакций (например, переводы)
    public function createTransaction(int $userId, string $type, float $amount, string $currency, array $metadata = [])
    {
        $transaction = Transaction::create([
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'metadata' => $metadata,
        ]);
        $user = User::find($userId);
        $user->notify(new TransactionNotification($transaction));

        return $transaction;
    }

    // Метод для проверки фрод-мониторинга
    public function checkFraud(Transaction $transaction)
    {
        $userId = $transaction->user_id;
        $amount = $transaction->amount;
        $currency = $transaction->currency;

        // Проверка частоты транзакций
        $transactionCount = Transaction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($transactionCount > 10) {
            $this->logSuspiciousActivity($transaction);

            return false; // Подозрительная активность
        }

        // Проверка на аномальную сумму транзакции
        $averageAmount = Transaction::where('user_id', $userId)
            ->where('currency', $currency)
            ->avg('amount');

        if (abs($amount - $averageAmount) > 1000) { // 1000 — порог, можно настроить
            $this->logSuspiciousActivity($transaction);

            return false; // Подозрительная активность
        }

        return true; // Транзакция не подозрительная
    }

    // Логирование подозрительных операций
    private function logSuspiciousActivity(Transaction $transaction)
    {
        Log::warning('Suspicious transaction detected', [
            'user_id' => $transaction->user_id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'type' => $transaction->type,
        ]);
    }

    public function processTransaction(int $userId, string $type, float $amount, string $currency, array $metadata = [])
    {
        $transaction = $this->createTransaction($userId, $type, $amount, $currency, $metadata);

        if (! $this->checkFraud($transaction)) {
            throw new \Exception('Transaction flagged as suspicious.');
        }

        return $transaction;
    }
}
