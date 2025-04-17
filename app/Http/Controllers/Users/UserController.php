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
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = $this->getFromCacheOrStore(self::CACHE_KEY_USERS, self::CACHE_MINUTES, function () {
                return $this->userService->getAll();
            });

            return $this->successResponse($users);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $cacheKey = self::CACHE_KEY_USER . $id;

            $user = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                $user = $this->userService->getById($id);

                if (!$user) {
                    throw new Exception('User not found', 404);
                }

                return $user;
            });

            return $this->successResponse($user);
        } catch (Exception $e) {
            $statusCode = $e->getCode() === 404 ? 404 : 500;
            $message = $e->getCode() === 404 ? 'User not found' : 'An error occurred while fetching the user: ' . $e->getMessage();

            return $this->errorResponse($message, $statusCode);
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RegisterRequest $request)
    {
        try {
            $userData = $request->validated();
            $user = $this->userService->create($userData);

            return $this->successResponse($this->authService->register($user, false), 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(),500);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $user->update([
            'name' => $request->username ?? $user->username,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? PasswordUtil::hash($request->password) : $user->password,
        ]);

        $this->forgetCache(self::CACHE_KEY_USER . $id);

        return $this->successResponse($user);
    }

    /**
     * Remove the specified user from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $user = $this->userService->getById($id);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            $this->userService->deleteUser($user);

            $this->forgetCache([
                self::CACHE_KEY_USER . $id,
                self::CACHE_KEY_USERS
            ]);

            return $this->successResponse(['message' => 'User deleted successfully']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Change user role.
     *
     * @return \Illuminate\Http\JsonResponse
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
