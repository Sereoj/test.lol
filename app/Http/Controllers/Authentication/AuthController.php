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
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected UserService $userService;
    protected AuthService $authService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER = 'user_short_';

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
            
            Log::info('User registered successfully', ['user_id' => $user->id]);
            
            $result = $this->authService->register($user, $request->input('remember_me', false));
            
            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage(), ['data' => $request->validated()]);
            return $this->errorResponse('User registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();
            
            $result = $this->authService->login($credentials);
            
            Log::info('User logged in successfully', ['email' => $credentials['email']]);
            
            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('An error occurred during login: ' . $e->getMessage(), ['email' => $request->email]);
            return $this->errorResponse('An error occurred during login: ' . $e->getMessage(), 500);
        }
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');
            
            $result = $this->authService->refreshToken($refreshToken);
            
            Log::info('Token refreshed successfully');
            
            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('An error occurred during token refresh: ' . $e->getMessage());
            return $this->errorResponse('An error occurred during token refresh: ' . $e->getMessage(), 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $cacheKey = self::CACHE_KEY_USER . $userId;
            
            $user = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return new UserShortResource($this->userService->findUserById($userId));
            });
            
            Log::info('User info retrieved successfully', ['user_id' => $userId]);
            
            return $this->successResponse($user);
        } catch (Exception $e) {
            Log::error('Error retrieving user info: ' . $e->getMessage(), ['user_id' => $request->user()->id]);
            return $this->errorResponse('Error retrieving user info: ' . $e->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $this->authService->logout($request->user());
            
            $cacheKey = self::CACHE_KEY_USER . $userId;
            $this->forgetCache($cacheKey);
            
            Log::info('User logged out successfully', ['user_id' => $userId]);
            
            return $this->successResponse(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            Log::error('An error occurred during logout: ' . $e->getMessage(), ['user_id' => $request->user()->id ?? null]);
            return $this->errorResponse('An error occurred during logout: ' . $e->getMessage(), 500);
        }
    }
}
