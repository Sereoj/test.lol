<?php

namespace App\Services\Authentication;

use App\Mail\AccountRecoveryMail;
use App\Models\Users\User;
use App\Services\Users\UserAccountService;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AccountRecoveryService
{
    protected UserAccountService $userAccountService;

    /**
     * Конструктор сервиса восстановления аккаунта
     * 
     * @param UserAccountService $userAccountService
     */
    public function __construct(UserAccountService $userAccountService)
    {
        $this->userAccountService = $userAccountService;
    }

    /**
     * Отправка запроса на восстановление удаленного аккаунта
     * 
     * @param string $email Адрес электронной почты пользователя
     * @return array Результат операции с токеном для отладки
     * @throws Exception
     */
    public function sendRecoveryRequest(string $email): array
    {
        // Ищем пользователя, включая удаленных
        $user = User::withTrashed()
            ->where('email', $email)
            ->first();
        
        if (!$user) {
            // Возвращаем общее сообщение для безопасности
            return [
                'success' => true,
                'message' => 'Если аккаунт существует, инструкции по восстановлению будут отправлены на указанный email'
            ];
        }
        
        if (!$user->trashed()) {
            // Если аккаунт не удален, возвращаем общее сообщение
            return [
                'success' => true,
                'message' => 'Если аккаунт существует, инструкции по восстановлению будут отправлены на указанный email'
            ];
        }

        // Генерируем токен восстановления
        $token = $this->generateRecoveryToken($user->id);
        
        // Отправляем email с ссылкой для восстановления
        $this->sendRecoveryEmail($user, $token);
        
        // Возвращаем результат
        return [
            'success' => true,
            'message' => 'Если аккаунт существует, инструкции по восстановлению будут отправлены на указанный email',
            'debug_token' => $token // Удалить в продакшене!
        ];
    }
    
    /**
     * Восстановление аккаунта по токену
     * 
     * @param string $token Токен восстановления
     * @param string $password Новый пароль
     * @return array Результат операции
     * @throws Exception
     */
    public function recoverAccountByToken(string $token, string $password): array
    {
        // Получаем ID пользователя из кэша
        $userId = $this->getUserIdFromToken($token);
        
        if (!$userId) {
            throw new Exception('Недействительный или истекший токен восстановления');
        }
        
        // Ищем пользователя, включая удаленных
        $user = User::withTrashed()->find($userId);
        
        if (!$user) {
            throw new Exception('Пользователь не найден');
        }
        
        if (!$user->trashed()) {
            throw new Exception('Аккаунт не был удален');
        }
        
        // Обновляем пароль пользователя
        $user->password = Hash::make($password);
        
        // Восстанавливаем аккаунт
        $user->restore();
        $user->save();
        
        // Удаляем токен из кэша
        $this->invalidateToken($token);
        
        return [
            'success' => true,
            'message' => 'Аккаунт успешно восстановлен'
        ];
    }
    
    /**
     * Генерирует токен восстановления и сохраняет его в кэше
     * 
     * @param int $userId ID пользователя
     * @return string Токен восстановления
     */
    private function generateRecoveryToken(int $userId): string
    {
        $token = Str::random(60);
        
        // Сохраняем токен в кэше на 24 часа с привязкой к ID пользователя
        cache()->put('account_recovery_' . $token, $userId, 60 * 24);
        
        return $token;
    }
    
    /**
     * Отправка email с инструкциями по восстановлению
     * 
     * @param User $user Пользователь
     * @param string $token Токен восстановления
     * @return void
     */
    private function sendRecoveryEmail(User $user, string $token): void
    {
        Mail::to($user->email)->send(new AccountRecoveryMail($token));
    }
    
    /**
     * Получение ID пользователя из токена
     * 
     * @param string $token Токен восстановления
     * @return int|null ID пользователя или null, если токен недействителен
     */
    private function getUserIdFromToken(string $token): ?int
    {
        return cache()->get('account_recovery_' . $token);
    }
    
    /**
     * Удаление токена из кэша
     * 
     * @param string $token Токен восстановления
     * @return void
     */
    private function invalidateToken(string $token): void
    {
        cache()->forget('account_recovery_' . $token);
    }
    
    /**
     * Проверяет статус удаления аккаунта пользователя
     * 
     * @param string $email Email пользователя
     * @return array Информация о статусе аккаунта
     */
    public function checkAccountStatus(string $email): array
    {
        $user = User::withTrashed()->where('email', $email)->first();
        
        if (!$user) {
            return [
                'exists' => false,
                'deleted' => false,
                'message' => 'Аккаунт не найден'
            ];
        }
        
        return [
            'exists' => true,
            'deleted' => $user->trashed(),
            'deleted_at' => $user->deleted_at,
            'message' => $user->trashed() ? 'Аккаунт был удален' : 'Аккаунт активен'
        ];
    }
} 