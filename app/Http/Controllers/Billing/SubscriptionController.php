<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Acquiring\AnypayService;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

// Контроллер для работы с подписками
class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_ACTIVE_SUBSCRIPTION = 'active_subscription_user_';

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    // Получение активной подписки пользователя
    public function getActiveSubscription(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_ACTIVE_SUBSCRIPTION . $userId;

            $this->subscriptionService->checkAndUpdateSubscriptionStatus();

            $subscription = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () {
                return $this->subscriptionService->getActiveSubscription();
            });

            Log::info('Активная подписка успешно получена', ['user_id' => $userId]);

            return $this->successResponse($subscription);
        } catch (Exception $e) {
            Log::error('Ошибка при получении активной подписки: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Error retrieving active subscription: ' . $e->getMessage(), 500);
        }
    }

    // Создание новой подписки
    public function createSubscription(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan' => 'required|string',
                'amount' => 'required|numeric',
                'currency' => 'required|string|max:3',
                'duration' => 'required|integer',
            ]);

            $userId = Auth::id();

            $subscription = $this->subscriptionService->createSubscription(
                $validated['plan'],
                $validated['amount'],
                $validated['currency'],
                $validated['duration']
            );

            $this->forgetCache(self::CACHE_KEY_ACTIVE_SUBSCRIPTION . $userId);

            Log::info('Подписка успешно создана', [
                'user_id' => $userId,
                'plan' => $validated['plan'],
                'duration' => $validated['duration']
            ]);

            return $this->successResponse($subscription, 201);
        } catch (ValidationException $e) {
            Log::warning('Ошибка валидации при создании подписки', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Ошибка при создании подписки: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Error creating subscription: ' . $e->getMessage(), 500);
        }
    }

    // Продление подписки
    public function extendSubscription(Request $request, int $subscriptionId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'duration' => 'required|integer',
            ]);

            $userId = Auth::id();

            $this->subscriptionService->extendSubscription($subscriptionId, $validated['duration']);

            $this->forgetCache(self::CACHE_KEY_ACTIVE_SUBSCRIPTION . $userId);

            Log::info('Подписка успешно продлена', [
                'user_id' => $userId,
                'subscription_id' => $subscriptionId,
                'added_duration' => $validated['duration']
            ]);

            return $this->successResponse(['message' => 'Subscription extended successfully']);
        } catch (ValidationException $e) {
            Log::warning('Ошибка валидации при продлении подписки', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Ошибка при продлении подписки: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'subscription_id' => $subscriptionId
            ]);
            return $this->errorResponse('Error extending subscription: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Создание платежной ссылки для оплаты подписки через AnyPay
     */
    public function createPaymentLink(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan_id' => 'required|string|in:year,3months,trial',
                'amount' => 'required|numeric|min:1',
                'currency' => 'string|max:3',
            ]);

            $userId = Auth::id();
            $currency = $validated['currency'] ?? 'USD';

            $anypayService = app(AnypayService::class);
            $paymentData = $anypayService->createPaymentLink(
                $userId,
                $validated['amount'],
                $currency
            );

            // Сохраняем информацию о плане в метаданных транзакции
            $transactionRepository = app(\App\Repositories\TransactionRepository::class);
            $transaction = $transactionRepository->findById($paymentData['transaction_id']);

            if ($transaction) {
                $metadata = $transaction->metadata ?? [];
                $metadata['plan_id'] = $validated['plan_id'];
                $metadata['type'] = 'subscription';
                $transaction->metadata = $metadata;
                $transaction->save();
            }

            $this->logInfo('Ссылка на оплату создана для подписки', [
                'user_id' => $userId,
                'plan_id' => $validated['plan_id'],
                'amount' => $validated['amount'],
                'transaction_id' => $paymentData['transaction_id'],
            ]);

            return $this->successResponse($paymentData);
        } catch (ValidationException $e) {
            $this->logWarning('Ошибка валидации при создании ссылки на оплату', ['errors' => $e->errors()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            $this->logError('Ошибка при создании ссылки на оплату для подписки', [
                'error' => $e->getMessage(),
            ], $e);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
