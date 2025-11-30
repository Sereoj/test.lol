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

// Контроллер для работы с аккаунтом пользователя
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
     * @OA\Post(
     *     path="/api/v1/user/account/restore",
     *     tags={"Users"},
     *     summary="Restore user account",
     *     description="Restore user account",
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
