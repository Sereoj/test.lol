<?php

namespace App\Services\Billing;

use App\Models\Billing\Fee;
use App\Models\Users\UserBalance;
use App\Notifications\TransactionNotification;
use App\Repositories\PurchaseRepository;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseService
{
    protected PurchaseRepository $purchaseRepository;
    protected TransactionRepository $transactionRepository;

    public function __construct(
        PurchaseRepository $purchaseRepository,
        TransactionRepository $transactionRepository
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function purchasePost(int $postId, float $amount, string $currency, ?string $idempotencyKey = null)
    {
        $user = Auth::user();

        // Проверяем idempotency key для защиты от дубликатов
        if ($idempotencyKey) {
            $existingPurchase = $this->purchaseRepository->findByIdempotencyKey($idempotencyKey);
            if ($existingPurchase) {
                Log::info("Покупка с idempotency key уже существует", [
                    'idempotency_key' => $idempotencyKey,
                    'purchase_id' => $existingPurchase->id
                ]);
                return $existingPurchase;
            }
        }

        // Проверяем, не был ли пост уже куплен этим пользователем
        $existingPurchase = $this->purchaseRepository->findByPostIdAndUserId($postId, $user->id);
        if ($existingPurchase && $existingPurchase->status === 'completed') {
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

        return DB::transaction(function () use ($user, $postId, $amount, $totalAmount, $currency, $platformFee, $idempotencyKey) {
            // Проверяем баланс снова внутри транзакции (double-check)
            $userBalance = UserBalance::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $userBalance || $userBalance->balance < $totalAmount) {
                throw new Exception('Недостаточно средств.');
            }

            // Списываем средства с баланса
            $userBalance->balance -= $totalAmount;
            $userBalance->save();

            // Создаём запись о покупке
            $purchase = $this->purchaseRepository->create([
                'user_id' => $user->id,
                'post_id' => $postId,
                'amount' => $amount,
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
            ]);

            // Создаём запись транзакции
            $transaction = $this->transactionRepository->create([
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

    /**
     * Получить покупки пользователя с пагинацией
     */
    public function getUserPurchases(int $userId, int $page = 1, int $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        return $this->purchaseRepository->findByUserIdWithPagination($userId, $limit, $offset);
    }

    /**
     * Проверить, куплен ли пост пользователем
     */
    public function isPostPurchasedByUser(int $postId, int $userId): bool
    {
        $purchase = $this->purchaseRepository->findByPostIdAndUserId($postId, $userId);
        return $purchase && $purchase->status === 'completed';
    }
}
