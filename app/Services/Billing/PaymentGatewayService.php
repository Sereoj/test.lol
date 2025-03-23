<?php

namespace App\Services\Billing;

use App\Exceptions\GatewayErrorException;
use App\Services\Acquiring\AnypayService;
use App\Services\Acquiring\EnotService;
use App\Services\Acquiring\SelectionService;
use App\Services\Acquiring\TinkoffService;
use App\Services\Base\SimpleService;
use Exception;

/**
 * Сервис для обработки платежей через различные шлюзы
 */
class PaymentGatewayService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'payment_gateway';
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('PaymentGatewayService');
    }
    
    /**
     * Обработать платеж через указанный шлюз
     *
     * @param int $userId ID пользователя
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @param string $gateway Платежный шлюз
     * @param float|null $fee Комиссия
     * @return array|string Результат операции
     * @throws GatewayErrorException
     */
    public function processPayment($userId, $amount, $currency, $gateway, $fee = null)
    {
        $this->logInfo('Обработка платежа', [
            'user_id' => $userId,
            'amount' => $amount,
            'currency' => $currency,
            'gateway' => $gateway,
            'fee' => $fee
        ]);
        
        try {
            $result = null;
            
            switch ($gateway) {
                case 'anypay':
                    $service = resolve(AnypayService::class);
                    $result = $service->processTopup($userId, $amount, $currency, $fee);
                    break;
                    
                case 'selection':
                    $service = resolve(SelectionService::class);
                    $result = $service->processTopup($userId, $amount, $currency, $fee);
                    break;
                    
                case 'enot':
                    $service = resolve(EnotService::class);
                    $result = $service->processTopup($userId, $amount, $currency, $fee);
                    break;
                    
                case 'tinkoff':
                    $service = resolve(TinkoffService::class);
                    $result = $service->processTopup($userId, $amount, $currency, $fee);
                    break;
                    
                default:
                    $this->logWarning('Неподдерживаемый платежный шлюз', ['gateway' => $gateway]);
                    throw new GatewayErrorException("Неподдерживаемый платежный шлюз: {$gateway}");
            }
            
            $this->logInfo('Платеж успешно обработан', [
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => $currency,
                'gateway' => $gateway
            ]);
            
            return $result;
        } catch (GatewayErrorException $e) {
            $this->logError('Ошибка шлюза при обработке платежа', [
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => $currency,
                'gateway' => $gateway
            ], $e);
            
            throw $e;
        } catch (Exception $e) {
            $this->logError('Ошибка при обработке платежа', [
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => $currency,
                'gateway' => $gateway
            ], $e);
            
            throw new GatewayErrorException("Ошибка при обработке платежа: {$e->getMessage()}", 0, $e);
        }
    }
}
