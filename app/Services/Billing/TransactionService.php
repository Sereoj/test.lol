<?php

namespace App\Services\Billing;

use App\Models\Billing\Topup;
use App\Models\Billing\Transaction;
use App\Models\Users\User;
use App\Models\Users\UserBalance;
use App\Notifications\TransactionNotification;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

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
        $transaction = $this->transactionRepository->create([
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

    /**
     * Успешное завершение пополнения баланса
     */
    public function debitSuccess(int $transactionId, float $amount): bool
    {
        return DB::transaction(function () use ($transactionId, $amount) {
            $transaction = $this->transactionRepository->findById($transactionId);

            if (!$transaction) {
                throw new Exception("Транзакция с ID {$transactionId} не найдена");
            }

            if ($transaction->status === 'succeeded') {
                Log::warning("Транзакция {$transactionId} уже обработана");
                return false;
            }

            // Обновляем статус транзакции
            $this->transactionRepository->updateStatus($transactionId, 'succeeded');

            // Обновляем баланс пользователя
            $userBalance = UserBalance::where('user_id', $transaction->user_id)
                ->where('currency', $transaction->currency)
                ->first();

            if (!$userBalance) {
                throw new Exception("Баланс пользователя {$transaction->user_id} не найден");
            }

            $userBalance->balance += $amount;
            $userBalance->save();

            // Создаем запись о пополнении
            Topup::create([
                'user_id' => $transaction->user_id,
                'amount' => $amount,
                'fee' => 0,
                'currency' => $transaction->currency,
                'gateway' => $transaction->metadata['gateway'] ?? 'anypay',
                'status' => 'succeeded',
            ]);

            // Уведомляем пользователя
            $user = User::find($transaction->user_id);
            $user->notify(new TransactionNotification($transaction));

            Log::info("Пополнение успешно завершено", [
                'transaction_id' => $transactionId,
                'user_id' => $transaction->user_id,
                'amount' => $amount,
            ]);

            return true;
        });
    }

    /**
     * Неудачное завершение пополнения баланса
     */
    public function debitFail(int $transactionId): bool
    {
        $transaction = $this->transactionRepository->findById($transactionId);

        if (!$transaction) {
            throw new Exception("Транзакция с ID {$transactionId} не найдена");
        }

        if ($transaction->status === 'failed') {
            Log::warning("Транзакция {$transactionId} уже помечена как неудачная");
            return false;
        }

        $this->transactionRepository->updateStatus($transactionId, 'failed');

        Log::info("Пополнение завершено с ошибкой", [
            'transaction_id' => $transactionId,
            'user_id' => $transaction->user_id,
        ]);

        return true;
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
