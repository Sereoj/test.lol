<?php

namespace App\Services\Acquiring;

use App\Models\Billing\Topup;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\Http;

// Сервис для работы с Anypay
class AnypayService implements IAcquiringService
{
    protected string $client = 'anypay';
    protected TransactionRepository $transactionRepository;

    protected array $config;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->config = config('services.anypay');
    }

    /**
     * Создание платежной ссылки для пополнения
     */
    public function createPaymentLink(int $userId, float $amount, string $currency): array
    {
        // Проверка минимальной суммы через API курсов валют
        $convertationSign = $this->generateSha256("rates{$this->config['api_id']}{$this->config['api_key']}");

        $ratesResponse = Http::asMultipart()->post("{$this->config['api_url']}/api/rates/{$this->config['api_id']}", [
            'sign' => $convertationSign,
        ]);

        if (!$ratesResponse->successful()) {
            throw new Exception('Не удалось получить курсы валют от AnyPay');
        }

        $rates = $ratesResponse->json();
        $minAmountInUsd = 300;
        $amountInUsd = $amount * ($rates['result']['in']['usd'] ?? 1);

        if ($amountInUsd < $minAmountInUsd) {
            $minAmountInCurrency = ceil($minAmountInUsd / ($rates['result']['in']['usd'] ?? 1));
            throw new Exception("Минимальная сумма для пополнения: {$minAmountInCurrency} {$currency}");
        }

        // Создаем транзакцию со статусом pending
        $transaction = $this->transactionRepository->create([
            'user_id' => $userId,
            'type' => 'topup',
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'metadata' => [
                'gateway' => $this->getGateway(),
            ],
        ]);

        // Генерируем подпись для платежа
        $signString = "{$this->config['project_id']}:{$transaction->id}:{$amount}:USD:::::{$this->config['secret_key']}";
        $sign = $this->generateSha256($signString);

        // Формируем параметры для платежной ссылки
        $paymentParams = [
            'merchant_id' => $this->config['project_id'],
            'pay_id' => $transaction->id,
            'amount' => $amount,
            'currency' => 'USD',
            'sign' => $sign,
        ];

        $paymentUrl = "{$this->config['api_url']}/merchant?" . http_build_query($paymentParams);

        return [
            'transaction_id' => $transaction->id,
            'payment_url' => $paymentUrl,
            'amount' => $amount,
            'currency' => $currency,
        ];
    }

    public function processTopup(int $userId, float $amount, string $currency, float $fee)
    {
        return Topup::create([
            'user_id' => $userId,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'gateway' => $this->getGateway(),
            'status' => 'succeeded',
        ]);
    }

    public function getGateway()
    {
        return $this->client;
    }

    /**
     * Генерация SHA256 хеша
     */
    protected function generateSha256(string $data): string
    {
        return hash('sha256', $data);
    }
}
