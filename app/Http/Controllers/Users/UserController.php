<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Users\User;
use App\Services\Authentication\AuthService;
use App\Services\Users\UserService;
use App\Utils\PasswordUtil;
use Exception;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

// Контроллер для работы с пользователями
class UserController extends Controller
{
    protected UserService $userService;
    protected AuthService $authService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USERS = 'users';
    private const CACHE_KEY_USER = 'user_';

    public function __construct(UserService $userService, AuthService $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
    }

                                    /**
     * @OA\Put(
     *     path="/api/v1/users/{userId}/change-role",
     *     tags={"Users"},
     *     summary="ChangeRole user",
     *     description="ChangeRole user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="UserId",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource updated successfully")
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
public function changeRole(Request $request, $userId)
    {
        try {
            $request->validate(['role_id' => 'required|exists:roles,id']);

            $user = $this->userService->getById($userId);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $this->userService->changeUserRole($user, $request->role_id);

            $this->forgetCache([
                self::CACHE_KEY_USER . $userId,
                self::CACHE_KEY_USERS
            ]);

            return $this->successResponse(['message' => 'User role changed successfully']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
