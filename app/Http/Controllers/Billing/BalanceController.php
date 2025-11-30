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
use OpenApi\Attributes as OA;

// Контроллер для работы с балансом пользователя
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

    // Получение баланса пользователя   
    
    /**
     * @OA\Post(
     *     path="/api/v1/user/balance/withdraw",
     *     tags={"Balances"},
     *     summary="WithdrawBalance balance",
     *     description="WithdrawBalance balance",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
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
