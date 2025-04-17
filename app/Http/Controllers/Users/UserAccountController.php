<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserAccountRequest;
use App\Http\Requests\User\DeleteUserAccountRequest;
use App\Http\Resources\Users\UserAccountResource;
use App\Services\Users\UserAccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class UserAccountController extends Controller
{
    protected UserAccountService $userAccountService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_ACCOUNT = 'user_account_';

    public function __construct(UserAccountService $userAccountService)
    {
        $this->userAccountService = $userAccountService;
    }

    /**
     * Получить информацию об аккаунте пользователя
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_USER_ACCOUNT . $userId;
            
            $userAccount = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return new UserAccountResource($this->userAccountService->getUserById($userId));
            });

            Log::info('User account retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($userAccount);
        } catch (Exception $e) {
            Log::error('Error retrieving user account: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Обновление аккаунта пользователя
     */
    public function update(UpdateUserAccountRequest $request)
    {
        try {
            $userId = Auth::id();
            $validatedData = $request->validated();
            
            $userAccount = $this->userAccountService->updateUserAccount($userId, $validatedData);
            
            // Обновляем кэш
            $cacheKey = self::CACHE_KEY_USER_ACCOUNT . $userId;
            $this->forgetCache($cacheKey);
            
            Log::info('User account updated successfully', ['user_id' => $userId]);

            return $this->successResponse([
                'message' => 'Данные аккаунта успешно обновлены', 
                'user' => new UserAccountResource($userAccount)
            ]);
        } catch (Exception $e) {
            Log::error('Error updating user account: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $request->validated()
            ]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Удаление аккаунта пользователя
     */
    public function destroy(DeleteUserAccountRequest $request)
    {
        try {
            $userId = Auth::id();
            $validatedData = $request->validated();
            
            $this->userAccountService->deleteUserAccount($userId, $validatedData);
            
            // Очищаем кэш
            $cacheKey = self::CACHE_KEY_USER_ACCOUNT . $userId;
            $this->forgetCache($cacheKey);
            
            Log::info('User account deleted successfully', ['user_id' => $userId]);

            return $this->successResponse([
                'success' => true,
                'message' => 'Аккаунт успешно удален'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting user account: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Восстановление удаленного аккаунта пользователя
     * (используется только для авторизованных пользователей)
     */
    public function restore()
    {
        try {
            $userId = Auth::id();
            
            $user = $this->userAccountService->restoreUserAccount($userId, 'Восстановлено через аккаунт пользователя');
            
            // Обновляем кэш
            $cacheKey = self::CACHE_KEY_USER_ACCOUNT . $userId;
            $this->forgetCache($cacheKey);
            
            Log::info('User account restored successfully', ['user_id' => $userId]);

            return $this->successResponse([
                'message' => 'Аккаунт успешно восстановлен',
                'user' => new UserAccountResource($user)
            ]);
        } catch (Exception $e) {
            Log::error('Error restoring user account: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
} 