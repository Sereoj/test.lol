<?php

namespace App\Services\Billing;

use App\Models\Billing\Fee;
use App\Models\Billing\Topup;
use App\Models\Billing\Transaction;
use App\Models\Billing\Withdrawal;
use App\Models\Users\User;
use App\Models\Users\UserBalance;
use App\Notifications\TransactionNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

class BalanceService
{
    public function getUserBalance(int $userId, string $currency)
    {
        $userBalance = UserBalance::where('user_id', $userId)
            ->where('currency', $currency)
            ->first();

        if (! $userBalance) {
            return ['error' => 'Баланс не найден для указанной валюты.'];
        }

        return [
            'balance' => $userBalance->balance,
            'currency' => $userBalance->currency,
        ];
    }

    public function topUpBalance(float $amount, string $currency, string $gateway)
    {
        return DB::transaction(function () use ($amount, $currency, $gateway) {
            $user = Auth::user();
            $fee = Fee::getFeeByType('acquiring', $gateway);

            // Получаем баланс только в указанной валюте
            $userBalance = UserBalance::where('user_id', $user->id)
                ->where('currency', $currency)
                ->first();

            if (! $userBalance) {
                throw new \Exception('Баланс пользователя для указанной валюты не найден.');
            }

            $userBalance->balance += $amount - $fee->fixed_amount;
            $userBalance->save();

            $topup = Topup::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => $fee->fixed_amount,
                'currency' => $currency,
                'gateway' => $gateway,
                'status' => 'succeeded',
            ]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount' => $amount - $fee->fixed_amount,
                'currency' => $currency,
                'status' => 'succeeded',
                'metadata' => ['topup_id' => $topup->id],
            ]);

            $user->notify(new TransactionNotification($transaction));

            return $topup;
        });
    }

    public function transferBalance(int $senderId, int $recipientId, float $amount, string $currency)
    {
        return DB::transaction(function () use ($senderId, $recipientId, $amount, $currency) {
            // Проверяем баланс отправителя в указанной валюте
            $senderBalance = UserBalance::where('user_id', $senderId)
                ->where('currency', $currency)
                ->first();
            if (! $senderBalance) {
                throw new \Exception('Sender balance not found for specified currency.');
            }

            $recipientBalance = UserBalance::where('user_id', $recipientId)
                ->where('currency', $currency)
                ->first();
            if (! $recipientBalance) {
                throw new \Exception('Recipient balance not found for specified currency.');
            }

            if ($senderBalance->balance < $amount) {
                throw new \Exception('Insufficient funds.');
            }

            // Списываем у отправителя
            $senderBalance->balance -= $amount;
            $senderBalance->save();

            // Начисляем получателю
            $recipientBalance->balance += $amount;
            $recipientBalance->save();

            $senderTransaction = Transaction::create([
                'user_id' => $senderId,
                'type' => 'transfer',
                'amount' => -$amount,
                'currency' => $currency,
                'status' => 'completed',
                'metadata' => ['recipient_user_id' => $recipientId],
            ]);
            User::find($senderId)->notify(new TransactionNotification($senderTransaction));

            $recipientTransaction = Transaction::create([
                'user_id' => $recipientId,
                'type' => 'transfer',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'completed',
                'metadata' => ['sender_user_id' => $senderId],
            ]);
            User::find($recipientId)->notify(new TransactionNotification($recipientTransaction));

            return [
                'sender_balance' => $senderBalance->balance,
                'recipient_balance' => $recipientBalance->balance,
            ];
        });
    }

    private function checkSufficientFunds(float $balance, float $amount): void
    {
        if ($balance < $amount) {
            throw new \Exception('Недостаточно средств');
        }
    }

    public function withdrawBalance(float $amount, string $currency)
    {
        return DB::transaction(function () use ($amount, $currency) {
            $user = Auth::user();

            // Получаем баланс пользователя
            $userBalance = UserBalance::where('user_id', $user->id)
                ->where('currency', $currency)
                ->first();
            if (! $userBalance) {
                throw new \Exception('Баланс пользователя не найден для указанной валюты.');
            }

            // Проверяем достаточность средств
            $this->checkSufficientFunds($userBalance->balance, $amount);

            // Вычитаем сумму со счета пользователя
            $userBalance->balance -= $amount;
            $userBalance->save();

            // Получаем комиссию за вывод
            $fee = Fee::getFeeByType('withdrawal');
            if (! $fee) {
                throw new \Exception('Комиссия за вывод не настроена.');
            }

            // Создаём запись о выводе средств
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'currency' => $currency,
                'fee' => $fee->fixed_amount ?? 0,
                'status' => 'pending',
            ]);

            // Создаём запись транзакции
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'currency' => $currency,
                'status' => 'pending',
                'metadata' => ['withdrawal_id' => $withdrawal->id],
            ]);

            // Уведомляем пользователя
            $user->notify(new TransactionNotification($transaction));

            return $withdrawal;
        });
    }

    /**
     * Выплата продавцу
     *
     * @param int $userId ID пользователя (продавца)
     *
     */
    public function payoutToSeller(int $userId)
    {
        Log::info("Начало выплаты продавцу", ['user_id' => $userId]);

        return DB::transaction(function () use ($userId) {
            // Получаем баланс пользователя
            $userBalance = UserBalance::where('user_id', $userId)->first();

            if (!$userBalance) {
                Log::warning("Баланс продавца не найден", ['user_id' => $userId]);
                throw new \Exception("Баланс продавца не найден");
            }

            if ($userBalance->pending_balance <= 0) {
                Log::warning("Нет средств для выплаты", ['user_id' => $userId, 'pending_balance' => $userBalance->pending_balance]);
                throw new \Exception("Нет средств для выплаты");
            }

            $amount = $userBalance->pending_balance;
            $currency = $userBalance->currency;

            // Переводим из ожидающего баланса в основной
            $userBalance->balance += $amount;
            $userBalance->pending_balance = 0;
            $userBalance->save();

            // Создаем запись о транзакции
            $transaction = Transaction::create([
                'user_id' => $userId,
                'type' => 'payout',
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'completed',
                'metadata' => ['payout_date' => now()],
            ]);

            // Отправляем уведомление
            User::find($userId)->notify(new TransactionNotification($transaction));

            Log ("Выплата продавцу успешно выполнена", [
                'user_id' => $userId,
                'amount' => $amount,
                'transaction_id' => $transaction->id
            ]);

            return [
                'success' => true,
                'amount' => $amount,
                'currency' => $currency,
                'transaction_id' => $transaction->id
            ];
        });
    }
}
