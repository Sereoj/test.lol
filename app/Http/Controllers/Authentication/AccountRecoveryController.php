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
     * Конструктор контроллера восстановления аккаунта
     * 
     * @param AccountRecoveryService $accountRecoveryService
     */
    public function __construct(AccountRecoveryService $accountRecoveryService)
    {
        $this->accountRecoveryService = $accountRecoveryService;
    }

    /**
     * Проверка статуса аккаунта (активен/удален)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $email = $request->input('email');
            $result = $this->accountRecoveryService->checkAccountStatus($email);
            
            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('Ошибка проверки статуса аккаунта: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Запрос на восстановление удаленного аккаунта
     * 
     * @param RequestRecoveryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestRecovery(RequestRecoveryRequest $request)
    {
        try {
            $email = $request->input('email');
            $result = $this->accountRecoveryService->sendRecoveryRequest($email);
            
            Log::info('Запрос на восстановление аккаунта отправлен', ['email' => $email]);
            
            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('Ошибка запроса восстановления аккаунта: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    
    /**
     * Восстановление удаленного аккаунта по токену
     * 
     * @param RecoverAccountRequest $request
     * @return \Illuminate\Http\JsonResponse
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
