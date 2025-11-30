<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

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
    /**
     * @OA\Get(
     *     path="/api/v1/user/transactions",
     *     tags={"Transactions"},
     *     summary="GetTransactions transaction",
     *     description="GetTransactions transaction",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_USER_TRANSACTIONS . $userId;
            
            $transactions = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return $this->transactionService->getUserTransactions($userId);
            });
            
            Log::info('User transactions retrieved successfully', ['user_id' => $userId]);
            
            return $this->successResponse($transactions);
        } catch (Exception $e) {
            Log::error('Error retrieving user transactions: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Error retrieving user transactions: ' . $e->getMessage(), 500);
        }
    }
}
