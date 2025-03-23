<?php

namespace App\Services\Billing;

use App\Exceptions\ResourceNotFoundException;
use App\Models\Billing\Subscription;
use App\Services\Base\SimpleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Сервис для работы с подписками
 */
class SubscriptionService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'subscription';
    
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
        $this->setLogPrefix('SubscriptionService');
    }
    
    /**
     * Получить активную подписку пользователя
     *
     * @return Subscription|null
     */
    public function getActiveSubscription()
    {
        $user = Auth::user();
        if (!$user) {
            $this->logWarning('Пользователь не авторизован при получении активной подписки');
            return null;
        }
        
        $cacheKey = $this->buildCacheKey('active', [$user->id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($user) {
            $this->logInfo('Получение активной подписки', ['user_id' => $user->id]);
            
            try {
                $subscription = Subscription::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
                
                if ($subscription) {
                    $this->logInfo('Найдена активная подписка', [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'expires_at' => $subscription->expires_at
                    ]);
                } else {
                    $this->logInfo('Активная подписка не найдена', ['user_id' => $user->id]);
                }
                
                return $subscription;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении активной подписки', [
                    'user_id' => $user->id
                ], $e);
                
                return null;
            }
        });
    }
    
    /**
     * Создать подписку
     *
     * @param string $plan Тип плана
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @param int $duration Продолжительность подписки в днях
     * @return Subscription|null
     */
    public function createSubscription($plan, $amount, $currency, $duration)
    {
        $user = Auth::user();
        if (!$user) {
            $this->logWarning('Пользователь не авторизован при создании подписки');
            return null;
        }
        
        $this->logInfo('Создание подписки', [
            'user_id' => $user->id,
            'plan' => $plan,
            'amount' => $amount,
            'currency' => $currency,
            'duration' => $duration
        ]);
        
        return $this->transaction(function () use ($user, $plan, $amount, $currency, $duration) {
            try {
                $expiresAt = Carbon::now()->addDays($duration);
                
                $subscription = Subscription::query()->create([
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'active',
                    'purchased_at' => Carbon::now(),
                    'expires_at' => $expiresAt,
                ]);
                
                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('active', [$user->id]),
                    $this->buildCacheKey('all', [$user->id])
                ]);
                
                $this->logInfo('Подписка создана', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'expires_at' => $expiresAt
                ]);
                
                return $subscription;
            } catch (Exception $e) {
                $this->logError('Ошибка при создании подписки', [
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'amount' => $amount,
                    'currency' => $currency,
                    'duration' => $duration
                ], $e);
                
                return null;
            }
        });
    }
    
    /**
     * Обновить статус подписки
     *
     * @param int $subscriptionId ID подписки
     * @param string $status Новый статус
     * @return Subscription|null
     * @throws ResourceNotFoundException
     */
    public function updateSubscriptionStatus($subscriptionId, $status = 'inactive')
    {
        $this->logInfo('Обновление статуса подписки', [
            'subscription_id' => $subscriptionId,
            'status' => $status
        ]);
        
        return $this->transaction(function () use ($subscriptionId, $status) {
            try {
                $subscription = Subscription::query()->find($subscriptionId);
                
                if (!$subscription) {
                    $this->logWarning('Подписка не найдена', ['subscription_id' => $subscriptionId]);
                    throw new ResourceNotFoundException('Подписка не найдена');
                }
                
                $subscription->update(['status' => $status]);
                
                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('active', [$subscription->user_id]),
                    $this->buildCacheKey('all', [$subscription->user_id]),
                    $this->buildCacheKey('id', [$subscriptionId])
                ]);
                
                $this->logInfo('Статус подписки обновлен', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $subscription->user_id,
                    'status' => $status
                ]);
                
                return $subscription;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при обновлении статуса подписки', [
                    'subscription_id' => $subscriptionId
                ], $e);
                
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при обновлении статуса подписки', [
                    'subscription_id' => $subscriptionId,
                    'status' => $status
                ], $e);
                
                return null;
            }
        });
    }
    
    /**
     * Продлить подписку
     *
     * @param int $subscriptionId ID подписки
     * @param int $duration Продолжительность подписки в днях
     * @return Subscription|null
     * @throws ResourceNotFoundException
     */
    public function extendSubscription($subscriptionId, $duration)
    {
        $this->logInfo('Продление подписки', [
            'subscription_id' => $subscriptionId,
            'duration' => $duration
        ]);
        
        return $this->transaction(function () use ($subscriptionId, $duration) {
            try {
                $subscription = Subscription::query()->find($subscriptionId);
                
                if (!$subscription) {
                    $this->logWarning('Подписка не найдена', ['subscription_id' => $subscriptionId]);
                    throw new ResourceNotFoundException('Подписка не найдена');
                }
                
                if ($subscription->status !== 'active') {
                    $this->logWarning('Попытка продлить неактивную подписку', [
                        'subscription_id' => $subscriptionId,
                        'status' => $subscription->status
                    ]);
                    return null;
                }
                
                $newExpiresAt = $subscription->expires_at > Carbon::now()
                    ? Carbon::parse($subscription->expires_at)->addDays($duration)
                    : Carbon::now()->addDays($duration);
                
                $subscription->update([
                    'expires_at' => $newExpiresAt,
                    'status' => 'active'
                ]);
                
                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('active', [$subscription->user_id]),
                    $this->buildCacheKey('all', [$subscription->user_id]),
                    $this->buildCacheKey('id', [$subscriptionId])
                ]);
                
                $this->logInfo('Подписка продлена', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $subscription->user_id,
                    'new_expires_at' => $newExpiresAt
                ]);
                
                return $subscription;
            } catch (ResourceNotFoundException $e) {
                $this->logError('Ресурс не найден при продлении подписки', [
                    'subscription_id' => $subscriptionId
                ], $e);
                
                throw $e;
            } catch (Exception $e) {
                $this->logError('Ошибка при продлении подписки', [
                    'subscription_id' => $subscriptionId,
                    'duration' => $duration
                ], $e);
                
                return null;
            }
        });
    }
    
    /**
     * Проверить и обновить статус подписки
     *
     * @return bool
     */
    public function checkAndUpdateSubscriptionStatus()
    {
        $user = Auth::user();
        if (!$user) {
            $this->logWarning('Пользователь не авторизован при проверке статуса подписки');
            return false;
        }
        
        $this->logInfo('Проверка статуса подписки', ['user_id' => $user->id]);
        
        try {
            $subscription = $this->getActiveSubscription();
            
            if (!$subscription) {
                $this->logInfo('Активная подписка не найдена для обновления статуса', ['user_id' => $user->id]);
                return false;
            }
            
            if ($subscription->expires_at <= Carbon::now()) {
                $this->updateSubscriptionStatus($subscription->id, 'expired');
                
                $this->logInfo('Подписка отмечена как истекшая', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id
                ]);
                
                return true;
            }
            
            $this->logInfo('Подписка активна, обновление статуса не требуется', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'expires_at' => $subscription->expires_at
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logError('Ошибка при проверке и обновлении статуса подписки', [
                'user_id' => $user ? $user->id : null
            ], $e);
            
            return false;
        }
    }
    
    /**
     * Получить все подписки пользователя
     *
     * @param int|null $userId ID пользователя (если null, используется текущий пользователь)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUserSubscriptions($userId = null)
    {
        $userId = $userId ?: (Auth::user() ? Auth::user()->id : null);
        
        if (!$userId) {
            $this->logWarning('ID пользователя не указан при получении всех подписок');
            return collect();
        }
        
        $cacheKey = $this->buildCacheKey('all', [$userId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение всех подписок пользователя', ['user_id' => $userId]);
            
            try {
                $subscriptions = Subscription::query()
                    ->where('user_id', $userId)
                    ->orderBy('purchased_at', 'desc')
                    ->get();
                
                $this->logInfo('Получены все подписки пользователя', [
                    'user_id' => $userId,
                    'count' => $subscriptions->count()
                ]);
                
                return $subscriptions;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении всех подписок пользователя', [
                    'user_id' => $userId
                ], $e);
                
                return collect();
            }
        });
    }
    
    /**
     * Получить подписку по ID
     *
     * @param int $subscriptionId ID подписки
     * @return Subscription|null
     */
    public function getSubscriptionById($subscriptionId)
    {
        $cacheKey = $this->buildCacheKey('id', [$subscriptionId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($subscriptionId) {
            $this->logInfo('Получение подписки по ID', ['subscription_id' => $subscriptionId]);
            
            try {
                $subscription = Subscription::query()->find($subscriptionId);
                
                if (!$subscription) {
                    $this->logWarning('Подписка не найдена', ['subscription_id' => $subscriptionId]);
                    return null;
                }
                
                $this->logInfo('Получена подписка по ID', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $subscription->user_id
                ]);
                
                return $subscription;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении подписки по ID', [
                    'subscription_id' => $subscriptionId
                ], $e);
                
                return null;
            }
        });
    }
}
