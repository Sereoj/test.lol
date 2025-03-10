<?php

namespace App\Services\Acquiring;

use App\Models\Billing\Topup;

class SelectionService implements IAcquiringService
{
    protected string $client = 'selection';
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
}
