<?php

namespace App\Services\Acquiring;

use App\Models\Billing\Topup;

class AnypayService
{
    public function processTopup(int $userId, float $amount, string $currency, float $fee)
    {
        // Здесь будет логика обработки платежей через Anypay
        return Topup::create([
            'user_id' => $userId,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'gateway' => 'anypay',
            'status' => 'succeeded',
        ]);
    }
}
