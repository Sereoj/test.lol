<?php

namespace App\Services\Billing;

use App\Models\Billing\Fee;
use App\Models\Billing\Purchase;
use App\Models\Billing\Transaction;
use App\Models\Users\UserBalance;
use App\Notifications\TransactionNotification;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseService
{
    public function purchasePost(int $postId, float $amount, string $currency)
    {
        return DB::transaction(function () use ($postId, $amount, $currency) {
            $user = Auth::user();

            // Проверяем, не был ли пост уже куплен этим пользователем
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('post_id', $postId)
                ->where('status', 'completed')
                ->first();
            if ($existingPurchase) {
                throw new Exception('Этот пост уже был куплен.');
            }

            // Получаем баланс пользователя
            $userBalance = UserBalance::where('user_id', $user->id)->first();
            if (! $userBalance) {
                throw new Exception('Баланс пользователя не найден.');
            }

            // Получаем комиссию платформы
            $platformFee = Fee::where('type', 'platform')->first();
            if (! $platformFee) {
                throw new Exception('Комиссия платформы не настроена.');
            }

            $totalAmount = $amount + $platformFee->fixed_amount;

            // Проверяем, достаточно ли средств на балансе
            if ($userBalance->balance < $totalAmount) {
                throw new Exception('Недостаточно средств для покрытия суммы покупки и комиссии.');
            }

            try {
                $paymentGatewayService = new PaymentGatewayService();
                $paymentGatewayService->processPayment($user->id, $totalAmount, $currency, 'anypay', $platformFee->fixed_amount);
            } catch (Exception $e) {
                throw new Exception('Ошибка при обработке платежа: '.$e->getMessage());
            }

            // Списываем средства с баланса
            $userBalance->balance -= $totalAmount;
            $userBalance->save();

            // Создаём запись о покупке
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'post_id' => $postId,
                'amount' => $amount,
                'status' => 'completed',
            ]);

            // Создаём запись транзакции
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => -$totalAmount,
                'currency' => $currency,
                'status' => 'completed',
                'metadata' => ['purchase_id' => $purchase->id, 'post_id' => $postId],
            ]);

            // Уведомляем пользователя
            $user->notify(new TransactionNotification($transaction));

            Log::info("Пользователь {$user->id} совершил покупку поста ID={$postId} на сумму {$totalAmount} {$currency}");

            return $purchase;
        });
    }
}
