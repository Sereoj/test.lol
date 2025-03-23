<?php

namespace App\Services\Billing;

use App\Models\Billing\Fee;
use App\Models\Billing\Topup;
use App\Models\Billing\Transaction;
use App\Models\Billing\Withdrawal;
use App\Models\Users\User;
use App\Models\Users\UserBalance;
use App\Notifications\TransactionNotification;
use App\Services\Base\SimpleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'balance';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('BalanceService');
    }

    /**
     * Получить баланс пользователя
     *
     * @param int $userId ID пользователя
     * @param string $currency Валюта
     * @return array
     */
    public function getUserBalance(int $userId, string $currency): array
    {
        $cacheKey = $this->buildCacheKey('balance', [$userId, $currency]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId, $currency) {
            $this->logInfo("Получение баланса пользователя", ['user_id' => $userId, 'currency' => $currency]);

            $userBalance = UserBalance::where('user_id', $userId)
                ->where('currency', $currency)
                ->first();

            if (! $userBalance) {
                $this->logWarning("Баланс не найден", ['user_id' => $userId, 'currency' => $currency]);
                return ['error' => 'Баланс не найден для указанной валюты.'];
            }

            return [
                'balance' => $userBalance->balance,
                'currency' => $userBalance->currency,
            ];
        });
    }

    /**
     * Пополнить баланс пользователя
     *
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @param string $gateway Шлюз оплаты
     * @return mixed
     */
    public function topUpBalance(float $amount, string $currency, string $gateway): mixed
    {
        $this->logInfo("Начало пополнения баланса", [
            'amount' => $amount,
            'currency' => $currency,
            'gateway' => $gateway
        ]);

        return $this->transaction(function () use ($amount, $currency, $gateway) {
            $user = Auth::user();
            $fee = Fee::getFeeByType('acquiring', $gateway);

            // Получаем баланс только в указанной валюте
            $userBalance = UserBalance::where('user_id', $user->id)
                ->where('currency', $currency)
                ->first();

            if (! $userBalance) {
                $this->logWarning("Баланс не найден при пополнении", ['user_id' => $user->id, 'currency' => $currency]);
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

            $this->forgetCache($this->buildCacheKey('balance', [$user->id, $currency]));

            $this->logInfo("Баланс успешно пополнен", [
                'user_id' => $user->id,
                'amount' => $amount,
                'topup_id' => $topup->id
            ]);

            return $topup;
        });
    }

    /**
     * Перевести средства между пользователями
     *
     * @param int $senderId ID отправителя
     * @param int $recipientId ID получателя
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @return array
     */
    public function transferBalance(int $senderId, int $recipientId, float $amount, string $currency): array
    {
        $this->logInfo("Начало перевода средств", [
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'amount' => $amount,
            'currency' => $currency
        ]);

        return $this->transaction(function () use ($senderId, $recipientId, $amount, $currency) {
            // Проверяем баланс отправителя в указанной валюте
            $senderBalance = UserBalance::where('user_id', $senderId)
                ->where('currency', $currency)
                ->first();
            if (! $senderBalance) {
                $this->logWarning("Баланс отправителя не найден", ['user_id' => $senderId, 'currency' => $currency]);
                throw new \Exception('Sender balance not found for specified currency.');
            }

            $recipientBalance = UserBalance::where('user_id', $recipientId)
                ->where('currency', $currency)
                ->first();
            if (! $recipientBalance) {
                $this->logWarning("Баланс получателя не найден", ['user_id' => $recipientId, 'currency' => $currency]);
                throw new \Exception('Recipient balance not found for specified currency.');
            }

            if ($senderBalance->balance < $amount) {
                $this->logWarning("Недостаточно средств для перевода", ['user_id' => $senderId, 'balance' => $senderBalance->balance, 'amount' => $amount]);
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

            // Сбрасываем кеш балансов
            $this->forgetCache($this->buildCacheKey('balance', [$senderId, $currency]));
            $this->forgetCache($this->buildCacheKey('balance', [$recipientId, $currency]));

            $this->logInfo("Перевод успешно выполнен", [
                'sender_id' => $senderId,
                'recipient_id' => $recipientId,
                'amount' => $amount
            ]);

            return [
                'sender_balance' => $senderBalance->balance,
                'recipient_balance' => $recipientBalance->balance,
            ];
        });
    }

    /**
     * Проверить достаточность средств
     *
     * @param float $balance Баланс
     * @param float $amount Сумма
     * @return void
     */
    private function checkSufficientFunds(float $balance, float $amount): void
    {
        if ($balance < $amount) {
            throw new \Exception('Недостаточно средств');
        }
    }

    /**
     * Вывести средства с баланса
     *
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @return mixed
     */
    public function withdrawBalance(float $amount, string $currency)
    {
        $this->logInfo("Начало вывода средств", ['amount' => $amount, 'currency' => $currency]);

        return $this->transaction(function () use ($amount, $currency) {
            $user = Auth::user();

            // Получаем баланс пользователя
            $userBalance = UserBalance::where('user_id', $user->id)
                ->where('currency', $currency)
                ->first();
            if (! $userBalance) {
                $this->logWarning("Баланс не найден при выводе средств", ['user_id' => $user->id, 'currency' => $currency]);
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
                $this->logWarning("Комиссия за вывод не настроена");
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

            // Сбрасываем кеш баланса
            $this->forgetCache($this->buildCacheKey('balance', [$user->id, $currency]));

            $this->logInfo("Вывод средств в обработке", [
                'user_id' => $user->id,
                'amount' => $amount,
                'withdrawal_id' => $withdrawal->id
            ]);

            return $withdrawal;
        });
    }

    /**
     * Выплата продавцу
     *
     * @param int $userId ID пользователя (продавца)
     * @return array
     */
    public function payoutToSeller(int $userId)
    {
        $this->logInfo("Начало выплаты продавцу", ['user_id' => $userId]);

        return $this->transaction(function () use ($userId) {
            // Получаем баланс пользователя
            $userBalance = UserBalance::where('user_id', $userId)->first();

            if (!$userBalance) {
                $this->logWarning("Баланс продавца не найден", ['user_id' => $userId]);
                throw new \Exception("Баланс продавца не найден");
            }

            if ($userBalance->pending_balance <= 0) {
                $this->logWarning("Нет средств для выплаты", ['user_id' => $userId, 'pending_balance' => $userBalance->pending_balance]);
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

            // Сбрасываем кеш баланса
            $this->forgetCache($this->buildCacheKey('balance', [$userId, $currency]));

            $this->logInfo("Выплата продавцу успешно выполнена", [
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
