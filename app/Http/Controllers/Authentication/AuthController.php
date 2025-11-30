<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Users\UserLongResource;
use App\Http\Resources\Users\UserShortWithBalanceResource;
use App\Services\Authentication\AuthService;
use App\Services\Users\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    protected AuthService $authService;
    protected UserService $userService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_USER = 'user_';

    public function __construct(AuthService $authService, UserService $userService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
    }

    // Выход из системы
    
    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout auth",
     *     description="Logout auth",
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
public function logout(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $this->authService->logout($request->user());

            $cacheKey = self::CACHE_KEY_USER . $userId;
            $this->forgetCache($cacheKey);

            Log::info('User logged out successfully', ['user_id' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Выход выполнен успешно'
            ]);
        } catch (Exception $e) {
            Log::error('An error occurred during logout: ' . $e->getMessage(), ['user_id' => $request->user()->id ?? null]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout: ' . $e->getMessage()
            ], 500);
        }
    }
}
