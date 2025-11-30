<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\RecoverAccountRequest;
use App\Http\Requests\Authentication\RequestRecoveryRequest;
use App\Services\Authentication\AccountRecoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;
use Exception;

class AccountRecoveryController extends Controller
{
    protected AccountRecoveryService $accountRecoveryService;

    public function __construct(AccountRecoveryService $accountRecoveryService)
    {
        $this->accountRecoveryService = $accountRecoveryService;
    }

    // Восстановление аккаунта по токену
    
    /**
     * @OA\Post(
     *     path="/api/v1/account/recovery/recover",
     *     tags={"AccountRecoveries"},
     *     summary="RecoverAccount account recovery",
     *     description="RecoverAccount account recovery",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RecoverAccountRequest")
     *     ),
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
public function recoverAccount(RecoverAccountRequest $request)
    {
        try {
            $token = $request->input('token');
            $password = $request->input('password');
            
            $result = $this->accountRecoveryService->recoverAccountByToken($token, $password);
            
            Log::info('Аккаунт успешно восстановлен через токен');
            
            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('Ошибка восстановления аккаунта: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
