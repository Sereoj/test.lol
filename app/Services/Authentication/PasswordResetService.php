<?php

namespace App\Services\Authentication;

use App\Models\Authentication\PasswordReset;
use App\Models\Users\User;
use App\Services\Users\UserService;
use App\Traits\LoggableTrait;
use App\Utils\CodeGenerator;
use App\Utils\PasswordUtil;
use DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    use LoggableTrait;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function sendPasswordResetEmail(string $email): void
    {
        $this->logInfo('Попытка отправки email для сброса пароля', ['email' => $email]);

        try {
            $locale = app()->getLocale();
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->logInfo('Пользователь найден для сброса пароля', [
                'email' => $email,
                'user_id' => $user->id
            ]);

            PasswordReset::query()->where('email', $user->email)->delete();

            $token = CodeGenerator::generate(60);

            PasswordReset::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            $this->logInfo('Токен сброса пароля создан', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);

            Mail::send("emails.resets.$locale", ['token' => $token], function ($message) use ($user) {
                $message->to($user->email)->subject('Password Reset Request');
            });

            $this->logInfo('Email для сброса пароля успешно отправлен', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);

        } catch (ModelNotFoundException $e) {
            $this->logWarning('Попытка сброса пароля для несуществующего email', ['email' => $email]);
            throw new Exception('Пользователь с этим email не найден', 404);
        } catch (Exception $e) {
            $this->logError('Не удалось отправить email для сброса пароля', ['email' => $email], $e);
            throw $e;
        }
    }

    public function resetPassword(string $email, string $token, string $newPassword): void
    {
        $this->logInfo('Попытка сброса пароля', ['email' => $email]);

        $user = $this->userService->getByEmail($email);
        if (!$user) {
            $this->logWarning('Сброс пароля не удался: пользователь не найден', ['email' => $email]);
            throw new Exception('Пользователь не найден', 404);
        }

        $passwordReset = PasswordReset::query()->where('email', $user->email)
            ->where('token', $token)
            ->first();

        if (!$passwordReset) {
            $this->logWarning('Сброс пароля не удался: недействительный токен', [
                'email' => $email,
                'user_id' => $user->id
            ]);
            throw new Exception('Недействительный или истекший токен сброса пароля', 400);
        }

        try {
          //DB::beginTransaction(); не безопасен
            DB::transaction(function () use ($user, $newPassword) {
                $user->password = PasswordUtil::hash($newPassword);
                $user->save();
                PasswordReset::query()->where('email', $user->email)->delete();
            });

            $this->logInfo('Пароль успешно сброшен', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);

        } catch (Exception $e) {
            $this->logError('Сброс пароля не удался при операции с базой данных', [
                'email' => $email,
                'user_id' => $user->id
            ], $e);
            throw $e;
        }
    }
}
