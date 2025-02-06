<?php

namespace App\Services;

use App\Models\PasswordReset;
use App\Models\Users\User;
use App\Services\Users\UserService;
use App\Utils\CodeGenerator;
use App\Utils\PasswordUtil;
use Exception;
use Illuminate\Support\Facades\Mail;

class PasswordResetService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Send a password reset email to the user.
     *
     * @param  string  $email
     * @return void
     */
    public function sendPasswordResetEmail($email)
    {
        try {
            $locale = app()->getLocale();
            $user = User::query()->where('email', $email)->firstOrFail();
            $token = CodeGenerator::generate(60);

            // Сохраняем токен в базе данных или отправляем его в письме

            PasswordReset::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            // В данном примере мы просто отправляем токен в письме

            Mail::send('emails.resets.'.$locale, ['token' => $token], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset Request');
            });
        } catch (Exception $e) {
            throw new Exception('An error occurred while sending the password reset email.');
        }
    }

    /**
     * Reset the user's password.
     *
     * @param  string  $email
     * @param  string  $token
     * @param  string  $newPassword
     * @return void
     */
    public function resetPassword($email, $token, $newPassword)
    {
        try {
            $user = $this->userService->findUserByEmail($email);

            // Проверяем токен
            $passwordReset = PasswordReset::query()->where('email', $user->email)
                ->where('token', $token)
                ->first();

            if (! $passwordReset) {
                throw new Exception('Invalid token.');
            }

            $user->password = PasswordUtil::hash($newPassword);
            $user->save();

            PasswordReset::query()->where('email', $user->email)->delete();
        } catch (Exception $e) {
            throw new Exception('An error occurred while resetting the password.');
        }
    }
}
