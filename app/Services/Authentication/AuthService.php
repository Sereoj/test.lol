<?php

namespace App\Services\Authentication;

use App\Http\Resources\Users\UserShortResource;
use App\Models\User;
use App\Services\Base\SimpleService;
use App\Services\Users\TokenService;
use App\Services\Users\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Сервис аутентификации
 */
class AuthService extends SimpleService
{
    /**
     * Сервис для работы с пользователями
     *
     * @var UserService
     */
    protected UserService $userService;

    /**
     * Сервис для работы с токенами
     *
     * @var TokenService
     */
    protected TokenService $tokenService;
    
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'auth';

    /**
     * Конструктор
     *
     * @param UserService $userService
     * @param TokenService $tokenService
     */
    public function __construct(UserService $userService, TokenService $tokenService)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->tokenService = $tokenService;
        $this->setLogPrefix('AuthService');
    }

    /**
     * Выполнить вход пользователя
     *
     * @param array $credentials
     * @return array|null|\Illuminate\Http\JsonResponse
     */
    public function login(array $credentials)
    {
        $this->logInfo('Попытка входа', ['email' => $credentials['email']]);
        
        $user = $this->userService->findUserByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            $this->logWarning('Неверные учетные данные', ['email' => $credentials['email']]);
            return response()->json(['message' => 'Email или пароль неверны!'], 401);
        }

        if (!$this->attemptLogin($credentials)) {
            $this->logError('Неавторизованная попытка входа', ['email' => $credentials['email']]);
            return response()->json(['message' => 'Неавторизованный доступ'], 401);
        }

        $token = $this->tokenService->generateTokens($user);
        
        $this->logInfo('Успешный вход', ['user_id' => $user->id, 'email' => $user->email]);

        return [
            'user' => new UserShortResource($user),
            'token' => $token,
        ];
    }

    /**
     * Зарегистрировать и авторизовать пользователя
     *
     * @param User $user
     * @param bool $isLogin
     * @param bool $rememberMe
     * @return array
     */
    public function register(User $user, bool $isLogin = true, bool $rememberMe = false): array
    {
        $this->logInfo('Регистрация пользователя', ['email' => $user->email]);
        
        if ($isLogin) {
            Auth::login($user, $rememberMe);
            $this->logInfo('Автоматический вход после регистрации', ['user_id' => $user->id]);
        }
        
        $token = $this->tokenService->generateTokens($user);

        return [
            'user' => new UserShortResource($user),
            'token' => [...$token],
        ];
    }

    /**
     * Обновить токен
     *
     * @param string $refreshToken
     * @return array|null
     */
    public function refreshToken(string $refreshToken)
    {
        $this->logInfo('Запрос на обновление токена');
        return $this->tokenService->refreshToken($refreshToken);
    }

    /**
     * Попытка входа
     *
     * @param array $credentials
     * @return bool
     */
    public function attemptLogin(array $credentials): bool
    {
        return Auth::attempt($credentials);
    }

    /**
     * Выход пользователя
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $this->logInfo('Выход пользователя', ['user_id' => $user->id]);
        $user->tokens()->delete();
    }
}
