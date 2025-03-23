<?php

namespace App\Services\Users;

use App\Events\ProfileComplected;
use App\Models\Users\User;
use App\Services\Base\SimpleService;
use Exception;

/**
 * Сервис для работы с профилями пользователей
 */
class UserProfileService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'user_profile';
    
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
        $this->setLogPrefix('UserProfileService');
    }
    
    /**
     * Получить профиль пользователя
     *
     * @param int $userId ID пользователя
     * @return User|null
     */
    public function getUserProfile($userId)
    {
        $cacheKey = $this->buildCacheKey('profile', [$userId]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
            $this->logInfo('Получение профиля пользователя', ['user_id' => $userId]);
            
            try {
                $user = User::find($userId);
                
                if (!$user) {
                    $this->logWarning('Пользователь не найден', ['user_id' => $userId]);
                    return null;
                }
                
                $this->logInfo('Профиль пользователя получен', ['user_id' => $userId]);
                
                return $user;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении профиля пользователя', ['user_id' => $userId], $e);
                return null;
            }
        });
    }

    /**
     * Обновить профиль пользователя
     *
     * @param int $userId ID пользователя
     * @param array $data Данные для обновления
     * @return User|null
     */
    public function updateUserProfile($userId, array $data)
    {
        $this->logInfo('Обновление профиля пользователя', ['user_id' => $userId, 'data' => $data]);
        
        return $this->transaction(function () use ($userId, $data) {
            try {
                $user = User::find($userId);
                
                if (!$user) {
                    $this->logWarning('Пользователь не найден при обновлении профиля', ['user_id' => $userId]);
                    return null;
                }
                
                $user->update($data);
                
                // Очистка кеша
                $this->forgetCache($this->buildCacheKey('profile', [$userId]));
                
                // Проверка завершенности профиля
                if ($user->isProfileComplete()) {
                    $this->logInfo('Профиль пользователя завершен', ['user_id' => $userId]);
                    event(new ProfileComplected($user));
                }
                
                $this->logInfo('Профиль пользователя обновлен', ['user_id' => $userId]);
                
                return $user;
            } catch (Exception $e) {
                $this->logError('Ошибка при обновлении профиля пользователя', [
                    'user_id' => $userId,
                    'data' => $data
                ], $e);
                
                return null;
            }
        });
    }
    
    /**
     * Проверить заполненность профиля пользователя
     *
     * @param int $userId ID пользователя
     * @return bool
     */
    public function isProfileComplete($userId): bool
    {
        $user = $this->getUserProfile($userId);
        
        if (!$user) {
            $this->logWarning('Невозможно проверить заполненность профиля, пользователь не найден', ['user_id' => $userId]);
            return false;
        }
        
        $result = $user->isProfileComplete();
        
        $this->logInfo('Проверка заполненности профиля', [
            'user_id' => $userId,
            'is_complete' => $result
        ]);
        
        return $result;
    }
}
