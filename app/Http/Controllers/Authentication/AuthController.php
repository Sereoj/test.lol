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

    /**
     * Регистрация нового пользователя
     * 
     * Создание нового пользователя в системе и выдача токена авторизации.
     *
     * @param RegisterRequest $request Данные для регистрации пользователя
     * @return \Illuminate\Http\JsonResponse
     * 
     * @bodyParam username string required Имя пользователя. Example: johndoe
     * @bodyParam email string required Email пользователя. Example: john@example.com
     * @bodyParam password string required Пароль пользователя (минимум 8 символов). Example: password123
     * @bodyParam password_confirmation string required Подтверждение пароля. Example: password123
     * 
     * @response 201 {
     *  "success": true,
     *  "data": {
     *    "user": {
     *      "id": 1,
     *      "username": "johndoe",
     *      "email": "john@example.com",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      }
     *    },
     *    "token": {
     *      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *      "refresh_token": "def5020017f41b7d...",
     *      "expires_in": 3600
     *    }
     *  },
     *  "message": "Регистрация успешно завершена"
     * }
     * 
     * @response 422 {
     *  "success": false,
     *  "message": "Ошибка валидации",
     *  "errors": {
     *    "email": ["Пользователь с таким email уже существует."],
     *    "password": ["Пароль должен содержать не менее 8 символов."]
     *  }
     * }
     */
    public function register(RegisterRequest $request)
    {
        try {
            $userData = $request->validated();
            $user = $this->userService->create($userData);

            Log::info('User registered successfully', ['user_id' => $user->id]);

            $result = $this->authService->register($user, $request->input('remember_me', false));

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Регистрация успешно завершена'
            ], 201);
        } catch (Exception $e) {
            Log::error('User registration failed: ' . $e->getMessage(), ['data' => $request->validated()]);
            return response()->json([
                'success' => false,
                'message' => 'User registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Вход в систему
     * 
     * Авторизация пользователя по email и паролю.
     *
     * @param LoginRequest $request Данные для входа в систему
     * @return \Illuminate\Http\JsonResponse
     * 
     * @bodyParam email string required Email пользователя. Example: john@example.com
     * @bodyParam password string required Пароль пользователя. Example: password123
     * 
     * @response {
     *  "success": true,
     *  "data": {
     *    "user": {
     *      "id": 1,
     *      "username": "johndoe",
     *      "email": "john@example.com",
     *      "verification": false,
     *      "avatar": {
     *        "path": "avatars/default.png"
     *      },
     *      "wallet": {
     *        "balance": "100.00"
     *      }
     *    },
     *    "token": {
     *      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *      "refresh_token": "def5020017f41b7d...",
     *      "expires_in": 3600
     *    }
     *  },
     *  "message": "Вход выполнен успешно"
     * }
     * 
     * @response 401 {
     *  "success": false,
     *  "message": "Неверный email или пароль"
     * }
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();

            $result = $this->authService->login($credentials);

            Log::info('User logged in successfully', ['email' => $credentials['email']]);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Вход выполнен успешно'
            ]);
        } catch (Exception $e) {
            Log::error('An error occurred during login: ' . $e->getMessage(), ['email' => $request->email]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Обновление токена
     * 
     * Создание нового токена доступа по refresh токену.
     *
     * @param RefreshTokenRequest $request Запрос с refresh токеном
     * @return \Illuminate\Http\JsonResponse
     * 
     * @bodyParam refresh_token string required Refresh токен, полученный при авторизации. Example: def50200d5efd866...
     * 
     * @response {
     *  "success": true,
     *  "data": {
     *    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *    "refresh_token": "def5020017f41b7d...",
     *    "expires_in": 3600
     *  },
     *  "message": "Токен обновлен успешно"
     * }
     * 
     * @response 401 {
     *  "success": false,
     *  "message": "Недействительный refresh токен"
     * }
     */
    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');

            $result = $this->authService->refreshToken($refreshToken);

            Log::info('Token refreshed successfully');

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Токен обновлен успешно'
            ]);
        } catch (Exception $e) {
            Log::error('An error occurred during token refresh: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Получение информации о текущем пользователе
     * 
     * Получение данных авторизованного пользователя.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @authenticated
     * 
     * @response {
     *  "success": true,
     *  "data": {
     *    "id": 1,
     *    "username": "johndoe",
     *    "email": "john@example.com",
     *    "verification": false,
     *    "avatar": {
     *      "path": "avatars/default.png"
     *    },
     *    "wallet": {
     *      "balance": "100.00"
     *    }
     *  }
     * }
     * 
     * @response 401 {
     *  "success": false,
     *  "message": "Unauthenticated."
     * }
     */
    public function user(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $cacheKey = self::CACHE_KEY_USER . $userId;

            $user = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return new UserShortResource($this->userService->getById($userId));
            });

            Log::info('User info retrieved successfully', ['user_id' => $userId]);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving user info: ' . $e->getMessage(), ['user_id' => $request->user()->id]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Выход из системы
     * 
     * Завершение сеанса пользователя и отзыв токена.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @authenticated
     * 
     * @response {
     *  "success": true,
     *  "message": "Выход выполнен успешно"
     * }
     * 
     * @response 401 {
     *  "success": false,
     *  "message": "Unauthenticated."
     * }
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
