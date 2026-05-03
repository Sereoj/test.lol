<?php

namespace App\Services\Billing;

use App\Events\MediaSourcePurchased;
use App\Models\Billing\Fee;
use App\Models\Users\UserBalance;
use App\Notifications\TransactionNotification;
use App\Repositories\MediaPurchaseRepository;
use App\Repositories\MediaRepository;
use App\Repositories\TransactionRepository;
use App\Traits\LoggableTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MediaPurchaseService
{
    use LoggableTrait;

    protected MediaPurchaseRepository $mediaPurchaseRepository;
    protected MediaRepository $mediaRepository;
    protected TransactionRepository $transactionRepository;

    public function __construct(
        MediaPurchaseRepository $mediaPurchaseRepository,
        MediaRepository $mediaRepository,
        TransactionRepository $transactionRepository
    ) {
        $this->mediaPurchaseRepository = $mediaPurchaseRepository;
        $this->mediaRepository = $mediaRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Покупка исходного файла медиа
     */
    public function purchaseMediaSource(int $mediaId, string $currency, ?string $idempotencyKey = null)
    {
        $user = Auth::user();

        // Проверяем idempotency key для защиты от дубликатов
        if ($idempotencyKey) {
            $existingPurchase = $this->mediaPurchaseRepository->findByIdempotencyKey($idempotencyKey);
            if ($existingPurchase) {
                $this->logInfo('Покупка с idempotency key уже существует', [
                    'idempotency_key' => $idempotencyKey,
                    'purchase_id' => $existingPurchase->id
                ]);
                return $existingPurchase;
            }
        }

        // Получаем медиа
        $media = $this->mediaRepository->getById($mediaId);

        // Проверяем, что у медиа есть исходник
        if (!$media->has_source || !$media->source_price) {
            $this->logError('Попытка покупки медиа без исходника', [
                'media_id' => $mediaId,
                'user_id' => $user->id,
            ]);
            throw new Exception('У этого медиа нет исходного файла для продажи.');
        }

        // Проверяем, не был ли исходник уже куплен этим пользователем
        $existingPurchase = $this->mediaPurchaseRepository->findByMediaIdAndUserId($mediaId, $user->id);
        if ($existingPurchase && $existingPurchase->status === 'completed') {
            $this->logWarning('Попытка повторной покупки медиа', [
                'media_id' => $mediaId,
                'user_id' => $user->id,
                'existing_purchase_id' => $existingPurchase->id,
            ]);
            throw new Exception('Исходник этого медиа уже был куплен.');
        }

        // Проверяем, что пользователь не покупает свой собственный файл
        if ($media->user_id === $user->id) {
            $this->logWarning('Попытка покупки собственного медиа', [
                'media_id' => $mediaId,
                'user_id' => $user->id,
            ]);
            throw new Exception('Нельзя купить исходник собственного медиа.');
        }

        // Получаем баланс пользователя
        $userBalance = UserBalance::where('user_id', $user->id)->first();
        if (!$userBalance) {
            $this->logError('Баланс пользователя не найден', [
                'user_id' => $user->id,
            ]);
            throw new Exception('Баланс пользователя не найден.');
        }

        // Получаем комиссию платформы
        $platformFee = Fee::where('type', 'platform')->first();
        if (!$platformFee) {
            $this->logError('Комиссия платформы не настроена');
            throw new Exception('Комиссия платформы не настроена.');
        }

        $amount = $media->source_price;
        $totalAmount = $amount + $platformFee->fixed_amount;

        // Проверяем, достаточно ли средств на балансе
        if ($userBalance->balance < $totalAmount) {
            $this->logWarning('Недостаточно средств для покупки медиа', [
                'user_id' => $user->id,
                'balance' => $userBalance->balance,
                'required' => $totalAmount,
                'media_id' => $mediaId,
            ]);
            throw new Exception('Недостаточно средств для покрытия суммы покупки и комиссии.');
        }

        return DB::transaction(function () use ($user, $mediaId, $amount, $totalAmount, $currency, $platformFee, $idempotencyKey) {
            // Проверяем баланс снова внутри транзакции (double-check)
            $userBalance = UserBalance::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$userBalance || $userBalance->balance < $totalAmount) {
                throw new Exception('Недостаточно средств.');
            }

            // Списываем средства с баланса
            $userBalance->balance -= $totalAmount;
            $userBalance->save();

            // Создаём запись о покупке
            $purchase = $this->mediaPurchaseRepository->create([
                'user_id' => $user->id,
                'media_id' => $mediaId,
                'amount' => $amount,
                'status' => 'completed',
                'idempotency_key' => $idempotencyKey,
            ]);

            // Создаём запись транзакции
            $transaction = $this->transactionRepository->create([
                'user_id' => $user->id,
                'type' => 'media_purchase',
                'amount' => -$totalAmount,
                'currency' => $currency,
                'status' => 'completed',
                'metadata' => ['media_purchase_id' => $purchase->id, 'media_id' => $mediaId],
            ]);

            // Уведомляем пользователя
            $user->notify(new TransactionNotification($transaction));

            // Отправляем событие о покупке исходника
            event(new MediaSourcePurchased($purchase));

            $this->logInfo('Пользователь совершил покупку исходника медиа', [
                'user_id' => $user->id,
                'media_id' => $mediaId,
                'amount' => $totalAmount,
                'currency' => $currency,
                'purchase_id' => $purchase->id,
            ]);

            return $purchase;
        });
    }

    /**
     * Получить покупки медиа пользователя с пагинацией
     */
    public function getUserMediaPurchases(int $userId, int $page = 1, int $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        return $this->mediaPurchaseRepository->findByUserIdWithPagination($userId, $limit, $offset);
    }

    /**
     * Проверить, куплен ли исходник медиа пользователем
     */
    public function isMediaSourcePurchasedByUser(int $mediaId, int $userId): bool
    {
        return $this->mediaPurchaseRepository->exists($mediaId, $userId);
    }
}
