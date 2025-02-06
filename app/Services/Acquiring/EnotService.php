<?php

namespace App\Services\Acquiring;

use App\Models\Topup;

class EnotService
{
    public function processTopup(int $userId, float $amount, string $currency, float $fee)
    {
        // Логика для Enot
        return Topup::create([
            'user_id' => $userId,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'gateway' => 'enot',
            'status' => 'succeeded',
        ]);
    }
}
