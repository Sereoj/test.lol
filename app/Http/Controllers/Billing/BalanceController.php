<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\BalanceService;
use App\Services\Billing\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class BalanceController extends Controller
{
    protected BalanceService $balanceService;
    protected PaymentGatewayService $paymentGatewayService;
    
    private const CACHE_MINUTES = 5;
    private const CACHE_KEY_USER_BALANCE = 'user_balance_%s_%s';

    public function __construct(BalanceService $balanceService, PaymentGatewayService $paymentGatewayService)
    {
        $this->balanceService = $balanceService;
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function getBalance(Request $request): JsonResponse
    {
        try {
            $currency = $request->input('currency');
            $userId = Auth::id();

            if (!$currency || !is_string($currency) || strlen($currency) !== 3) {
                Log::warning('Invalid currency format', ['currency' => $currency, 'user_id' => $userId]);
                return $this->errorResponse('Некорректная или отсутствующая валюта', 400);
            }

            $currency = strtoupper($currency);
            $cacheKey = sprintf(self::CACHE_KEY_USER_BALANCE, $userId, $currency);
            
            $balance = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId, $currency) {
                return $this->balanceService->getUserBalance($userId, $currency);
            });

            if (isset($balance['error'])) {
                Log::warning('Balance retrieval error', ['currency' => $currency, 'user_id' => $userId, 'error' => $balance['error']]);
                return $this->errorResponse($balance['error'], 404);
            }
            
            Log::info('Balance retrieved successfully', ['currency' => $currency, 'user_id' => $userId]);
            return $this->successResponse(['balance' => $balance]);
        } catch (Exception $e) {
            Log::error('Error retrieving balance: ' . $e->getMessage(), [
                'currency' => $request->input('currency'), 
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse('Error retrieving balance: ' . $e->getMessage(), 500);
        }
    }

    public function topUpBalance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
                'gateway' => 'required|string',
            ]);

            $userId = Auth::id();
            $currency = strtoupper($validated['currency']);
            
            $topup = $this->balanceService->topUpBalance(
                $validated['amount'],
                $currency,
                $validated['gateway']
            );
            
            // Очищаем кеш баланса пользователя
            $cacheKey = sprintf(self::CACHE_KEY_USER_BALANCE, $userId, $currency);
            $this->forgetCache($cacheKey);
            
            Log::info('Balance topped up successfully', [
                'user_id' => $userId,
                'amount' => $validated['amount'],
                'currency' => $currency,
                'gateway' => $validated['gateway']
            ]);

            return $this->successResponse(['topup' => $topup]);
        } catch (ValidationException $e) {
            Log::warning('Validation error during balance top-up', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Error topping up balance: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function transferBalance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'recipient_id' => 'required|exists:users,id|different:user_id',
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
            ]);

            $userId = Auth::id();
            $recipientId = $validated['recipient_id'];
            $currency = strtoupper($validated['currency']);
            
            $transfer = $this->balanceService->transferBalance(
                $userId,
                $recipientId,
                $validated['amount'],
                $currency
            );
            
            // Очищаем кеш баланса отправителя и получателя
            $this->forgetCache([
                sprintf(self::CACHE_KEY_USER_BALANCE, $userId, $currency),
                sprintf(self::CACHE_KEY_USER_BALANCE, $recipientId, $currency)
            ]);
            
            Log::info('Balance transferred successfully', [
                'sender_id' => $userId,
                'recipient_id' => $recipientId,
                'amount' => $validated['amount'],
                'currency' => $currency
            ]);

            return $this->successResponse(['transfer' => $transfer]);
        } catch (ValidationException $e) {
            Log::warning('Validation error during balance transfer', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Error transferring balance: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function withdrawBalance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
            ]);

            $userId = Auth::id();
            $currency = strtoupper($validated['currency']);
            
            $withdrawal = $this->balanceService->withdrawBalance(
                $validated['amount'],
                $currency
            );
            
            // Очищаем кеш баланса пользователя
            $cacheKey = sprintf(self::CACHE_KEY_USER_BALANCE, $userId, $currency);
            $this->forgetCache($cacheKey);
            
            Log::info('Balance withdrawn successfully', [
                'user_id' => $userId,
                'amount' => $validated['amount'],
                'currency' => $currency
            ]);

            return $this->successResponse(['withdrawal' => $withdrawal]);
        } catch (ValidationException $e) {
            Log::warning('Validation error during balance withdrawal', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Error withdrawing balance: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
