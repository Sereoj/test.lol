<?php

namespace App\Services\Billing;

use App\Models\Billing\Fee;
use App\Models\Billing\Purchase;
use App\Models\Billing\Transaction;
use App\Models\Users\UserBalance;
use App\Notifications\TransactionNotification;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'purchase';

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
        $this->setLogPrefix('PurchaseService');
    }

    /**
     * Покупка поста
     *
     * @param int $postId ID поста
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @return Purchase
     * @throws Exception
     */
    public function purchasePost(int $postId, float $amount, string $currency)
    {
        $this->logInfo("Начало покупки поста", [
            'post_id' => $postId,
            'amount' => $amount,
            'currency' => $currency
        ]);
        
        return $this->transaction(function () use ($postId, $amount, $currency) {
            $user = Auth::user();

            // Проверяем, не был ли пост уже куплен этим пользователем
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('post_id', $postId)
                ->where('status', 'completed')
                ->first();
                
            if ($existingPurchase) {
                $this->logWarning("Попытка повторной покупки поста", [
                    'user_id' => $user->id,
                    'post_id' => $postId,
                    'purchase_id' => $existingPurchase->id
                ]);
                throw new Exception('Этот пост уже был куплен.');
            }

            // Получаем баланс пользователя
            $userBalance = UserBalance::where('user_id', $user->id)->first();
            if (! $userBalance) {
                $this->logWarning("Баланс пользователя не найден", ['user_id' => $user->id]);
                throw new Exception('Баланс пользователя не найден.');
            }

            // Получаем комиссию платформы
            $platformFee = Fee::where('type', 'platform')->first();
            if (! $platformFee) {
                $this->logWarning("Комиссия платформы не настроена");
                throw new Exception('Комиссия платформы не настроена.');
            }

            $totalAmount = $amount + $platformFee->fixed_amount;

            // Проверяем, достаточно ли средств на балансе
            if ($userBalance->balance < $totalAmount) {
                $this->logWarning("Недостаточно средств для покупки", [
                    'user_id' => $user->id,
                    'balance' => $userBalance->balance,
                    'required' => $totalAmount
                ]);
                throw new Exception('Недостаточно средств для покрытия суммы покупки и комиссии.');
            }

            try {
                $paymentGatewayService = new PaymentGatewayService();
                $paymentGatewayService->processPayment($user->id, $totalAmount, $currency, 'anypay', $platformFee->fixed_amount);
            } catch (Exception $e) {
                $this->logError("Ошибка при обработке платежа", [
                    'user_id' => $user->id,
                    'post_id' => $postId,
                    'error' => $e->getMessage()
                ], $e);
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
            try {
                $user->notify(new TransactionNotification($transaction));
            } catch (Exception $e) {
                $this->logWarning("Ошибка при отправке уведомления о покупке", [
                    'user_id' => $user->id,
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Очищаем кеш покупок пользователя
            $this->forgetCache($this->buildCacheKey('user_purchases', [$user->id]));
            
            $this->logInfo("Покупка поста успешно выполнена", [
                'user_id' => $user->id,
                'post_id' => $postId,
                'purchase_id' => $purchase->id,
                'amount' => $totalAmount,
                'currency' => $currency
            ]);

            return $purchase;
        });
    }
    
    /**
     * Получить покупки пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserPurchases(int $userId)
    {
        $cacheKey = $this->buildCacheKey('user_purchases', [$userId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo("Получение покупок пользователя", ['user_id' => $userId]);
            
            return Purchase::where('user_id', $userId)
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }
    
    /**
     * Получить покупку по ID
     *
     * @param int $purchaseId ID покупки
     * @return Purchase|null
     */
    public function getPurchaseById(int $purchaseId)
    {
        $cacheKey = $this->buildCacheKey('purchase', [$purchaseId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($purchaseId) {
            $this->logInfo("Получение покупки по ID", ['purchase_id' => $purchaseId]);
            
            $purchase = Purchase::find($purchaseId);
            
            if (!$purchase) {
                $this->logWarning("Покупка не найдена", ['purchase_id' => $purchaseId]);
                return null;
            }
            
            return $purchase;
        });
    }
    
    /**
     * Проверить, был ли пост куплен пользователем
     *
     * @param int $userId ID пользователя
     * @param int $postId ID поста
     * @return bool
     */
    public function hasUserPurchasedPost(int $userId, int $postId)
    {
        $cacheKey = $this->buildCacheKey('user_post_purchase', [$userId, $postId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId, $postId) {
            $this->logInfo("Проверка покупки поста пользователем", [
                'user_id' => $userId,
                'post_id' => $postId
            ]);
            
            $exists = Purchase::where('user_id', $userId)
                ->where('post_id', $postId)
                ->where('status', 'completed')
                ->exists();
                
            return $exists;
        });
    }
    
    /**
     * Отменить покупку
     *
     * @param int $purchaseId ID покупки
     * @return bool
     */
    public function cancelPurchase(int $purchaseId)
    {
        $this->logInfo("Начало отмены покупки", ['purchase_id' => $purchaseId]);
        
        return $this->transaction(function () use ($purchaseId) {
            $purchase = Purchase::find($purchaseId);
            
            if (!$purchase) {
                $this->logWarning("Покупка не найдена при отмене", ['purchase_id' => $purchaseId]);
                throw new Exception("Покупка с ID {$purchaseId} не найдена");
            }
            
            // Проверяем статус покупки
            if ($purchase->status !== 'completed') {
                $this->logWarning("Некорректный статус покупки для отмены", [
                    'purchase_id' => $purchaseId, 
                    'status' => $purchase->status
                ]);
                throw new Exception("Невозможно отменить покупку с текущим статусом");
            }
            
            // Обновляем статус покупки
            $purchase->status = 'cancelled';
            $purchase->save();
            
            // Сбрасываем кеш
            $this->forgetCache($this->buildCacheKey('purchase', [$purchaseId]));
            $this->forgetCache($this->buildCacheKey('user_purchases', [$purchase->user_id]));
            $this->forgetCache($this->buildCacheKey('user_post_purchase', [$purchase->user_id, $purchase->post_id]));
            
            $this->logInfo("Покупка успешно отменена", ['purchase_id' => $purchaseId]);
            
            return true;
        });
    }
}
