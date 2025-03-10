<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Users\UserLongResource;
use App\Http\Resources\Users\UserShortResource;
use App\Services\Authentication\AuthService;
use App\Services\Users\UserService;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected UserService $userService;

    protected AuthService $authService;

    public function __construct(UserService $userService, AuthService $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $userData = $request->validated();
            $user = $this->userService->createUser($userData);

            return $this->authService->register($user, $request->input('remember_me', false));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'User registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();

            return $this->authService->login($credentials);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred during login',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');

            return response()->json($this->authService->refreshToken($refreshToken));
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred during token refresh',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function user(Request $request)
    {
        $user = new UserShortResource($this->userService->findUserById($request->user()->id));
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return response()->json(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred during logout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
