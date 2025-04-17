<?php

namespace App\Services\Users;

use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Log;

class UserAccountService
{

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Получить пользователя по ID
     *
     * @param int $userId
     * @return User
     * @throws Exception
     */
    public function getUserById(int $userId): User
    {
        $user = $this->userService->getById($userId);

        if (!$user) {
            throw new Exception('Пользователь не найден');
        }

        return $user;
    }

    /**
     * Обновить аккаунт пользователя
     *
     * @param int $userId
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function updateUserAccount(int $userId, array $data): User
    {
        $user = $this->getUserById($userId);

        // Проверка текущего пароля, если нужно обновить пароль
        if (isset($data['new_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                throw new Exception('Текущий пароль неверен');
            }
            $user->password = Hash::make($data['new_password']);
            unset($data['new_password'], $data['current_password'], $data['new_password_confirmation']);
        }

        // Обновляем другие поля
        $fillableFields = [
            'username', 'slug', 'description', 'website',
            'gender', 'location_id', 'language', 'age'
        ];

        foreach ($fillableFields as $field) {
            if (isset($data[$field])) {
                $user->{$field} = $data[$field];
            }
        }

        $user->save();
        return $user;
    }

    /**
     * Удалить аккаунт пользователя
     *
     * @param int $userId
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function deleteUserAccount(int $userId, array $data): bool
    {
        $user = $this->getUserById($userId);

        // Проверка пароля
        if (!Hash::check($data['password'], $user->password)) {
            throw new Exception('Неверный пароль');
        }

        // Тихое удаление пользователя (soft delete)
        return $user->delete();
    }

    /**
     * Восстановить удаленный аккаунт пользователя
     *
     * @param int $userId ID пользователя
     * @param string|null $reason Причина восстановления (опционально)
     * @return User Восстановленный пользователь
     * @throws Exception
     */
    public function restoreUserAccount(int $userId, ?string $reason = null): User
    {
        // Ищем пользователя, включая удаленных
        $user = User::withTrashed()->find($userId);
        
        if (!$user) {
            throw new Exception('Пользователь не найден');
        }
        
        if (!$user->trashed()) {
            throw new Exception('Аккаунт пользователя не был удален');
        }
        
        // Восстанавливаем пользователя
        $user->restore();
        
        // Если указана причина восстановления, можно её логировать
        if ($reason) {
            Log::info('Аккаунт восстановлен: ' . $reason, ['user_id' => $userId]);
        }
        
        // Загружаем связанные данные для Resource
        $user->load('location');
        
        return $user;
    }
}
