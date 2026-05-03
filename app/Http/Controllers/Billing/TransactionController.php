<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

// Контроллер для работы с транзакциями
class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    private const CACHE_MINUTES = 5;
    private const CACHE_KEY_USER_TRANSACTIONS = 'user_transactions_';

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    // Получение транзакций пользователя
    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_USER_TRANSACTIONS . $userId;

            $transactions = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return $this->transactionService->getUserTransactions($userId);
            });

            Log::info('Транзакции пользователя успешно получены', ['user_id' => $userId]);

            return $this->successResponse($transactions);
        } catch (Exception $e) {
            Log::error('Ошибка при получении транзакций пользователя: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Error retrieving user transactions: ' . $e->getMessage(), 500);
        }
    }
}
