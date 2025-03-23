<?php

namespace App\Services\Authentication;

use App\Models\User;
use App\Models\VerificationToken;
use App\Notifications\PasswordResetNotification;
use App\Services\Base\SimpleService;
use App\Services\Users\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Сервис сброса пароля
 */
class PasswordResetService extends SimpleService
{
    /**
     * Сервис для работы с пользователями
     *
     * @var UserService
     */
    protected UserService $userService;
    
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'password_reset';
    
    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->setLogPrefix('PasswordResetService');
    }

    /**
     * Отправить ссылку для сброса пароля
     *
     * @param string $email
     * @return bool
     */
    public function sendResetLink(string $email): bool
    {
        $this->logInfo('Запрос на сброс пароля', ['email' => $email]);
        
        $user = $this->userService->findUserByEmail($email);

        if (!$user) {
            $this->logWarning('Пользователь не найден при запросе сброса пароля', ['email' => $email]);
            return false;
        }

        try {
            $token = $this->createToken($user);
            $user->notify(new PasswordResetNotification($token));
            
            $this->logInfo('Ссылка сброса пароля отправлена', ['user_id' => $user->id, 'email' => $email]);
            
            return true;
        } catch (\Exception $e) {
            $this->logError('Ошибка при отправке ссылки сброса пароля', [
                'user_id' => $user->id, 
                'email' => $email,
                'error' => $e->getMessage()
            ], $e);
            
            return false;
        }
    }

    /**
     * Создать токен для сброса пароля
     *
     * @param User $user
     * @return string
     */
    protected function createToken(User $user): string
    {
        $this->logInfo('Создание токена сброса пароля', ['user_id' => $user->id]);
        
        return $this->transaction(function () use ($user) {
            // Удаляем старые токены
            $user->verificationTokens()->where('type', 'password_reset')->delete();
            
            // Создаем новый токен
            $token = Str::random(64);
            $expiresAt = Carbon::now()->addHours(24);
            
            $verificationToken = new VerificationToken([
                'token' => $token,
                'type' => 'password_reset',
                'expires_at' => $expiresAt
            ]);
            
            $user->verificationTokens()->save($verificationToken);
            
            $this->logInfo('Токен сброса пароля создан', [
                'user_id' => $user->id,
                'expires_at' => $expiresAt
            ]);
            
            return $token;
        });
    }

    /**
     * Проверить токен сброса пароля
     *
     * @param string $token
     * @return User|null
     */
    public function validateToken(string $token): ?User
    {
        $this->logInfo('Проверка токена сброса пароля', ['token' => $token]);
        
        $verificationToken = VerificationToken::where('token', $token)
            ->where('type', 'password_reset')
            ->where('expires_at', '>', Carbon::now())
            ->first();
        
        if (!$verificationToken) {
            $this->logWarning('Недействительный или просроченный токен сброса пароля', ['token' => $token]);
            return null;
        }
        
        $user = $verificationToken->user;
        
        if (!$user) {
            $this->logWarning('Пользователь не найден для токена сброса пароля', ['token' => $token]);
            return null;
        }
        
        return $user;
    }

    /**
     * Сбросить пароль пользователя
     *
     * @param string $token
     * @param string $password
     * @return bool
     */
    public function resetPassword(string $token, string $password): bool
    {
        $this->logInfo('Попытка сброса пароля', ['token' => $token]);
        
        return $this->transaction(function () use ($token, $password) {
            $user = $this->validateToken($token);
            
            if (!$user) {
                return false;
            }
            
            $user->password = Hash::make($password);
            $result = $user->save();
            
            // Удаляем токен после использования
            VerificationToken::where('token', $token)->delete();
            
            if ($result) {
                $this->logInfo('Пароль успешно сброшен', ['user_id' => $user->id]);
                
                // Удаляем все токены доступа пользователя
                $user->tokens()->delete();
            }
            
            return $result;
        });
    }
}
