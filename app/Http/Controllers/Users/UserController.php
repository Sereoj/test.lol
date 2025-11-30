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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Request")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
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
