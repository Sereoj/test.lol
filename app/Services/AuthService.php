<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected UserService $userService;

    private TokenService $tokenService;

    public function __construct(UserService $userService, TokenService $tokenService)
    {
        $this->userService = $userService;
        $this->tokenService = $tokenService;
    }

    public function login(array $credentials)
    {
        $user = $this->userService->findUserByEmail($credentials['email']);
        Log::info('Login attempt', ['email' => $credentials['email']]);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            Log::warning('Invalid credentials', ['email' => $credentials['email']]);

            return response()->json(['message' => 'Email or Password is not correct!'], 401);
        }

        if (! $this->attemptLogin($credentials)) {
            Log::error('Unauthorized login attempt', ['email' => $credentials['email']]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = $this->tokenService->generateTokens($user);

        return response()->json([
            'user' => $user,
            'token' => [...$token],
        ]);
    }

    public function register(User $user, $isLogin = true)
    {
        if ($isLogin) {
            Auth::login($user, true);
        }
        $token = $this->tokenService->generateTokens($user);

        return response()->json([
            'user' => $user,
            'token' => [...$token],
        ], 201);
    }

    public function refreshToken(string $refreshToken)
    {
        return response()->json($this->tokenService->refreshToken($refreshToken));
    }

    public function attemptLogin($credentials): bool
    {
        return Auth::attempt($credentials);
    }

    public function logout($user): void
    {
        $user->tokens()->delete();
    }
}
