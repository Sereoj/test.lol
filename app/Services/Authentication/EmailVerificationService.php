<?php

namespace App\Services\Authentication;

use App\Models\User;
use App\Models\VerificationToken;
use App\Notifications\VerifyEmailNotification;
use App\Services\Base\SimpleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Сервис проверки электронной почты
 */
class EmailVerificationService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'email_verification';
    
    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('EmailVerificationService');
    }
    
    /**
     * Создать токен верификации
     *
     * @param User $user
     * @return string
     */
    public function createVerificationToken(User $user): string
    {
        $this->logInfo('Создание токена верификации', ['user_id' => $user->id]);
        
        return $this->transaction(function () use ($user) {
            // Удаляем старые токены
            $user->verificationTokens()->delete();
            
            // Создаем новый токен
            $token = Str::random(64);
            $expiresAt = Carbon::now()->addHours(24);
            
            $verificationToken = new VerificationToken([
                'token' => $token,
                'type' => 'email',
                'expires_at' => $expiresAt
            ]);
            
            $user->verificationTokens()->save($verificationToken);
            
            $this->logInfo('Токен верификации создан', [
                'user_id' => $user->id,
                'expires_at' => $expiresAt
            ]);
            
            return $token;
        });
    }
    
    /**
     * Отправить письмо с верификацией
     *
     * @param User $user
     * @return bool
     */
    public function sendVerificationEmail(User $user): bool
    {
        $this->logInfo('Отправка письма верификации', ['user_id' => $user->id, 'email' => $user->email]);
        
        try {
            $token = $this->createVerificationToken($user);
            $user->notify(new VerifyEmailNotification($token));
            
            $this->logInfo('Письмо верификации отправлено', ['user_id' => $user->id]);
            
            return true;
        } catch (\Exception $e) {
            $this->logError('Ошибка при отправке письма верификации', ['user_id' => $user->id, 'error' => $e->getMessage()], $e);
            
            return false;
        }
    }
    
    /**
     * Проверить токен верификации
     *
     * @param string $token
     * @return User|null
     */
    public function verifyEmail(string $token): ?User
    {
        $this->logInfo('Проверка токена верификации', ['token' => $token]);
        
        return $this->transaction(function () use ($token) {
            $verificationToken = VerificationToken::where('token', $token)
                ->where('type', 'email')
                ->where('expires_at', '>', Carbon::now())
                ->first();
            
            if (!$verificationToken) {
                $this->logWarning('Недействительный или просроченный токен верификации', ['token' => $token]);
                return null;
            }
            
            $user = $verificationToken->user;
            
            if (!$user) {
                $this->logWarning('Пользователь не найден для токена верификации', ['token' => $token]);
                return null;
            }
            
            // Обновляем пользователя и удаляем токен
            $user->email_verified_at = Carbon::now();
            $user->save();
            
            $verificationToken->delete();
            
            $this->logInfo('Email успешно подтвержден', ['user_id' => $user->id]);
            
            return $user;
        });
    }
    
    /**
     * Проверить, подтвержден ли email пользователя
     *
     * @param User $user
     * @return bool
     */
    public function isEmailVerified(User $user): bool
    {
        return $user->email_verified_at !== null;
    }
}
