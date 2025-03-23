<?php

namespace App\Services\Acquiring;

use App\Models\Billing\Topup;
use App\Services\Base\SimpleService;
use Illuminate\Support\Facades\Log;

/**
 * Базовый абстрактный класс для сервисов эквайринга
 */
abstract class AcquiringBaseService extends SimpleService implements IAcquiringService
{
    /**
     * Название платежной системы
     *
     * @var string
     */
    protected string $client;

    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'acquiring';

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('Acquiring_' . $this->getGateway());
    }

    /**
     * Получить название шлюза
     *
     * @return string
     */
    public function getGateway(): string
    {
        return $this->client;
    }

    /**
     * Обработать пополнение баланса
     *
     * @param int $userId ID пользователя
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @param float $fee Комиссия
     * @return Topup
     */
    public function processTopup(int $userId, float $amount, string $currency, float $fee): Topup
    {
        $this->logInfo("Пополнение баланса", [
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => $currency,
            'fee' => $fee,
            'gateway' => $this->getGateway()
        ]);
        
        return $this->transaction(function () use ($userId, $amount, $currency, $fee) {
            return Topup::create([
                'user_id' => $userId,
                'amount' => $amount,
                'fee' => $fee,
                'currency' => $currency,
                'gateway' => $this->getGateway(),
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Подтвердить транзакцию
     *
     * @param string $transactionId Идентификатор транзакции
     * @return bool
     */
    public function confirmTransaction(string $transactionId): bool
    {
        $this->logInfo("Подтверждение транзакции", [
            'transaction_id' => $transactionId,
            'gateway' => $this->getGateway()
        ]);
        
        $topup = Topup::where('transaction_id', $transactionId)
            ->where('gateway', $this->getGateway())
            ->first();
            
        if (!$topup) {
            $this->logWarning("Транзакция не найдена", [
                'transaction_id' => $transactionId,
                'gateway' => $this->getGateway()
            ]);
            return false;
        }
        
        return $this->transaction(function () use ($topup) {
            $topup->status = 'succeeded';
            return $topup->save();
        });
    }

    /**
     * Отклонить транзакцию
     *
     * @param string $transactionId Идентификатор транзакции
     * @param string|null $reason Причина отклонения
     * @return bool
     */
    public function declineTransaction(string $transactionId, ?string $reason = null): bool
    {
        $this->logInfo("Отклонение транзакции", [
            'transaction_id' => $transactionId,
            'gateway' => $this->getGateway(),
            'reason' => $reason
        ]);
        
        $topup = Topup::where('transaction_id', $transactionId)
            ->where('gateway', $this->getGateway())
            ->first();
            
        if (!$topup) {
            $this->logWarning("Транзакция не найдена", [
                'transaction_id' => $transactionId,
                'gateway' => $this->getGateway()
            ]);
            return false;
        }
        
        return $this->transaction(function () use ($topup, $reason) {
            $topup->status = 'failed';
            $topup->failure_reason = $reason;
            return $topup->save();
        });
    }
} 