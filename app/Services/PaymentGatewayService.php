<?php

namespace App\Services;

use App\Services\Acquiring\AnypayService;
use App\Services\Acquiring\EnotService;
use App\Services\Acquiring\SelectionService;
use App\Services\Acquiring\TinkoffService;

class PaymentGatewayService
{
    public function processPayment(int $userId, float $amount, string $currency, string $gateway, float $fee)
    {
        switch ($gateway) {
            case 'anypay':
                return (new AnypayService())->processTopup($userId, $amount, $currency, $fee);
            case 'selection':
                return (new SelectionService())->processTopup($userId, $amount, $currency, $fee);
            case 'enot':
                return (new EnotService())->processTopup($userId, $amount, $currency, $fee);
            case 'tinkoff':
                return (new TinkoffService())->processTopup($userId, $amount, $currency, $fee);
            default:
                throw new \Exception("Gateway not supported.");
        }
    }
}
