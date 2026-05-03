<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Обработка webhook от AnyPay
     */
    public function anypay(Request $request)
    {
        try {
            // Проверка IP адреса
            $allowedIps = ['185.162.128.38', '185.162.128.39', '185.162.128.88'];
            $clientIp = $request->ip();

            if (!in_array($clientIp, $allowedIps)) {
                Log::warning('AnyPay вебхук отклонен: неверный IP', ['ip' => $clientIp]);
                return response('bad ip', 403);
            }

            // Получаем данные от AnyPay
            $merchantId = $request->input('merchant_id');
            $payId = $request->input('pay_id'); // Наш transaction ID
            $amount = $request->input('amount');
            $currency = $request->input('currency');
            $profit = $request->input('profit'); // Сумма после комиссии
            $email = $request->input('email');
            $method = $request->input('method');
            $sign = $request->input('sign');
            $status = $request->input('status'); // 'paid' или 'fail'

            // Проверяем подпись
            $config = config('services.anypay');
            $expectedSign = hash('sha256', "{$merchantId}:{$payId}:{$amount}:{$currency}:{$profit}:{$config['secret_key']}");

            if ($sign !== $expectedSign) {
                Log::error('AnyPay вебхук: неверная подпись', [
                    'transaction_id' => $payId,
                    'expected' => $expectedSign,
                    'received' => $sign,
                ]);
                return response('invalid signature', 403);
            }

            Log::info('AnyPay вебхук получен', [
                'transaction_id' => $payId,
                'amount' => $amount,
                'profit' => $profit,
                'status' => $status,
                'method' => $method,
            ]);

            // Обрабатываем в зависимости от статуса
            if ($status === 'paid') {
                $this->transactionService->debitSuccess((int)$payId, (float)$profit);
                Log::info('AnyPay платеж успешен', ['transaction_id' => $payId]);
            } else {
                $this->transactionService->debitFail((int)$payId);
                Log::info('AnyPay платеж неуспешен', ['transaction_id' => $payId]);
            }

            return response('ok', 200);
        } catch (Exception $e) {
            Log::error('AnyPay вебхук ошибка', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);
            return response('error', 500);
        }
    }
}
