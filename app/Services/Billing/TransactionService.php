<?php

namespace App\Services\Billing;

use App\Models\Billing\Transaction;
use App\Models\Users\User;
use App\Notifications\TransactionNotification;
use App\Services\Base\SimpleService;
use Illuminate\Support\Facades\Log;

class TransactionService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'transaction';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 30;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('TransactionService');
    }

    /**
     * Получить транзакции пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTransactions(int $userId)
    {
        $cacheKey = $this->buildCacheKey('user_transactions', [$userId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo("Получение транзакций пользователя", ['user_id' => $userId]);
            
            return Transaction::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Создать транзакцию
     *
     * @param int $userId ID пользователя
     * @param string $type Тип транзакции
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @param array $metadata Метаданные
     * @return Transaction
     */
    public function createTransaction(int $userId, string $type, float $amount, string $currency, array $metadata = [])
    {
        $this->logInfo("Создание транзакции", [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency
        ]);
        
        return $this->transaction(function () use ($userId, $type, $amount, $currency, $metadata) {
            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => $type,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'metadata' => $metadata,
            ]);
            
            try {
                $user = User::findOrFail($userId);
                $user->notify(new TransactionNotification($transaction));
            } catch (\Exception $e) {
                $this->logWarning("Ошибка при отправке уведомления о транзакции", [
                    'user_id' => $userId,
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Сбросить кеш транзакций пользователя
            $this->forgetCache($this->buildCacheKey('user_transactions', [$userId]));
            
            $this->logInfo("Транзакция создана", [
                'transaction_id' => $transaction->id,
                'user_id' => $userId
            ]);
            
            return $transaction;
        });
    }

    /**
     * Проверить транзакцию на подозрительную активность
     *
     * @param Transaction $transaction Транзакция
     * @return bool Результат проверки
     */
    public function checkFraud(Transaction $transaction)
    {
        $this->logInfo("Проверка транзакции на фрод", ['transaction_id' => $transaction->id]);
        
        $userId = $transaction->user_id;
        $amount = $transaction->amount;
        $currency = $transaction->currency;

        // Проверка частоты транзакций
        $transactionCount = Transaction::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($transactionCount > 10) {
            $this->logSuspiciousActivity($transaction, 'Превышено количество транзакций за день');
            return false; // Подозрительная активность
        }

        // Проверка на аномальную сумму транзакции
        $averageAmount = Transaction::where('user_id', $userId)
            ->where('currency', $currency)
            ->avg('amount');

        if (abs($amount - $averageAmount) > 1000) { // 1000 — порог, можно настроить
            $this->logSuspiciousActivity($transaction, 'Аномальная сумма транзакции');
            return false; // Подозрительная активность
        }

        $this->logInfo("Транзакция прошла проверку на фрод", ['transaction_id' => $transaction->id]);
        return true; // Транзакция не подозрительная
    }

    /**
     * Логирование подозрительных операций
     *
     * @param Transaction $transaction Транзакция
     * @param string $reason Причина
     * @return void
     */
    private function logSuspiciousActivity(Transaction $transaction, string $reason = '')
    {
        $context = [
            'user_id' => $transaction->user_id,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'type' => $transaction->type,
            'reason' => $reason
        ];
        
        $this->logWarning('Обнаружена подозрительная транзакция', $context);
    }

    /**
     * Обработать транзакцию
     *
     * @param int $userId ID пользователя
     * @param string $type Тип транзакции
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @param array $metadata Метаданные
     * @return Transaction
     */
    public function processTransaction(int $userId, string $type, float $amount, string $currency, array $metadata = [])
    {
        $this->logInfo("Обработка транзакции", [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency
        ]);
        
        return $this->safeExecute(function () use ($userId, $type, $amount, $currency, $metadata) {
            $transaction = $this->createTransaction($userId, $type, $amount, $currency, $metadata);

            if (!$this->checkFraud($transaction)) {
                $transaction->status = 'rejected';
                $transaction->save();
                
                $this->logWarning("Транзакция отклонена как подозрительная", [
                    'transaction_id' => $transaction->id,
                    'user_id' => $userId
                ]);
                
                throw new \Exception('Транзакция отмечена как подозрительная.');
            }
            
            // Обновляем статус транзакции
            $transaction->status = 'completed';
            $transaction->save();
            
            $this->logInfo("Транзакция успешно обработана", [
                'transaction_id' => $transaction->id,
                'user_id' => $userId
            ]);
            
            return $transaction;
        }, [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency
        ]);
    }

    /**
     * Получить транзакцию по ID
     *
     * @param int $transactionId ID транзакции
     * @return Transaction|null
     */
    public function getTransactionById(int $transactionId)
    {
        $cacheKey = $this->buildCacheKey('transaction', [$transactionId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($transactionId) {
            $this->logInfo("Получение транзакции по ID", ['transaction_id' => $transactionId]);
            
            $transaction = Transaction::find($transactionId);
            
            if (!$transaction) {
                $this->logWarning("Транзакция не найдена", ['transaction_id' => $transactionId]);
                return null;
            }
            
            return $transaction;
        });
    }

    /**
     * Обновить статус транзакции
     *
     * @param int $transactionId ID транзакции
     * @param string $status Новый статус
     * @return Transaction|null
     */
    public function updateTransactionStatus(int $transactionId, string $status)
    {
        $this->logInfo("Обновление статуса транзакции", [
            'transaction_id' => $transactionId,
            'status' => $status
        ]);
        
        return $this->transaction(function () use ($transactionId, $status) {
            $transaction = Transaction::find($transactionId);
            
            if (!$transaction) {
                $this->logWarning("Транзакция не найдена при обновлении статуса", ['transaction_id' => $transactionId]);
                throw new \Exception("Транзакция с ID {$transactionId} не найдена");
            }
            
            $transaction->status = $status;
            $transaction->save();
            
            // Сбросить кеш
            $this->forgetCache($this->buildCacheKey('transaction', [$transactionId]));
            $this->forgetCache($this->buildCacheKey('user_transactions', [$transaction->user_id]));
            
            $this->logInfo("Статус транзакции обновлен", [
                'transaction_id' => $transactionId,
                'status' => $status
            ]);
            
            return $transaction;
        });
    }
}
