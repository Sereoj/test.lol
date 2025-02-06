<?php

namespace App\Services\Acquiring;

use App\Models\Topup;

class TinkoffService
{
    public function processTopup(int $userId, float $amount, string $currency, float $fee)
    {
        // Логика для Tinkoff
        return Topup::create([
            'user_id' => $userId,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'gateway' => 'tinkoff',
            'status' => 'succeeded',
        ]);
    }
}
