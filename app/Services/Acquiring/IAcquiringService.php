<?php

namespace App\Services\Acquiring;

interface IAcquiringService
{
    public function getGateway();
    public function processTopup(int $userId, float $amount, string $currency, float $fee);
}
