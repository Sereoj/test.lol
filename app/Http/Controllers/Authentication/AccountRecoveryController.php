<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\RecoverAccountRequest;
use App\Http\Requests\Authentication\RequestRecoveryRequest;
use App\Services\Authentication\AccountRecoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class AccountRecoveryController extends Controller
{
    protected AccountRecoveryService $accountRecoveryService;

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
     *             @OA\Property(property="data", ref="#/components/schemas/AccountRecovery")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
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
