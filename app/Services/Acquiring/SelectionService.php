<?php

namespace App\Services\Acquiring;

use App\Models\Topup;

class SelectionService
{
    public function processTopup(int $userId, float $amount, string $currency, float $fee)
    {
        // Логика для Selection
        return Topup::create([
            'user_id' => $userId,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'gateway' => 'selection',
            'status' => 'succeeded',
        ]);
    }
}
