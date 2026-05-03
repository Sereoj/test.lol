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

/**
 * @group Аутентификация
 *
 * API для регистрации, авторизации и управления токенами
 */
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

    // Регистрация нового пользователя
    public function register(RegisterRequest $request)
    {
        try {
            $userData = $request->validated();
            $user = $this->userService->create($userData);

            Log::info('Пользователь успешно зарегистрирован', ['user_id' => $user->id]);

            $result = $this->authService->register($user, $request->input('remember_me', false));

            return $this->successResponse($result, [], 201);
        } catch (Exception $e) {
            Log::error('Регистрация пользователя не удалась: ' . $e->getMessage(), ['data' => [
                'email' => $userData['email'],
            ]]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Авторизация пользователя
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();

            $result = $this->authService->login($credentials);

            Log::info('Пользователь успешно вошел', ['email' => $credentials['email']]);

            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('Произошла ошибка при входе: ' . $e->getMessage(), ['email' => $request->email]);
            $statusCode = $e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    // Обновление токена
    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');

            $result = $this->authService->refreshToken($refreshToken);
            Log::info('Токен успешно обновлен');

            return $this->successResponse($result);
        } catch (Exception $e) {
            Log::error('Произошла ошибка при обновлении токена: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Получение информации о пользователе
    public function user(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $cacheKey = self::CACHE_KEY_USER . $userId;

            $user = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return new UserShortWithBalanceResource($this->userService->getById($userId));
            });

            Log::info('Информация о пользователе успешно получена', ['user_id' => $userId]);

            return $this->successResponse($user);
        } catch (Exception $e) {
            Log::error('Ошибка при получении информации о пользователе: ' . $e->getMessage(), ['user_id' => $request->user()->id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Выход из системы
    public function logout(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $this->authService->logout($request->user());

            $cacheKey = self::CACHE_KEY_USER . $userId;
            $this->forgetCache($cacheKey);

            Log::info('Пользователь успешно вышел', ['user_id' => $userId]);

            return response()->json([
                'success' => true,
                'message' => 'Выход выполнен успешно'
            ]);
        } catch (Exception $e) {
            Log::error('Произошла ошибка при выходе: ' . $e->getMessage(), ['user_id' => $request->user()->id ?? null]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout: ' . $e->getMessage()
            ], 500);
        }
    }
}
