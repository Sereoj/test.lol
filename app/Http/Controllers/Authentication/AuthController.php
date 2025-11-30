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

        /**
     * @OA\Get(
     *     path="/api/v1/user/me",
     *     tags={"Authentication"},
     *     summary="User auth",
     *     description="User auth",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Auth")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function user(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $cacheKey = self::CACHE_KEY_USER . $userId;

            $user = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return new UserShortWithBalanceResource($this->userService->getById($userId));
            });

            Log::info('User info retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($user);
        } catch (Exception $e) {
            Log::error('Error retrieving user info: ' . $e->getMessage(), ['user_id' => $request->user()->id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Выход из системы   
    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout auth",
     *     description="Logout auth",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Request")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Auth")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
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
