<?php

namespace App\Services\Acquiring;

use App\Models\Billing\Topup;
use Exception;

class EnotService implements IAcquiringService
{
    protected string $client = 'enot';

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

    public function createPaymentLink(int $userId, float $amount, string $currency): array
    {
        throw new Exception('Метод createPaymentLink не реализован для Enot');
    }

    public function getGateway()
    {
        return $this->client;
    }
}
