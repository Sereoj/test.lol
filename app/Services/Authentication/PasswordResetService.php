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
        $this->logInfo('Attempting to send password reset email', ['email' => $email]);

        try {
            $locale = app()->getLocale();
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->logInfo('User found for password reset', [
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

            $this->logInfo('Password reset token created', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);

            Mail::send("emails.resets.$locale", ['token' => $token], function ($message) use ($user) {
                $message->to($user->email)->subject('Password Reset Request');
            });

            $this->logInfo('Password reset email sent successfully', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);

        } catch (ModelNotFoundException $e) {
            $this->logWarning('Password reset attempt for non-existent email', ['email' => $email]);
            throw new Exception('User with this email not found', 404);
        } catch (Exception $e) {
            $this->logError('Failed to send password reset email', ['email' => $email], $e);
            throw $e;
        }
    }

    public function resetPassword(string $email, string $token, string $newPassword): void
    {
        $this->logInfo('Attempting to reset password', ['email' => $email]);

        $user = $this->userService->getByEmail($email);
        if (!$user) {
            $this->logWarning('Password reset failed: user not found', ['email' => $email]);
            throw new Exception('User not found', 404);
        }

        $passwordReset = PasswordReset::query()->where('email', $user->email)
            ->where('token', $token)
            ->first();

        if (!$passwordReset) {
            $this->logWarning('Password reset failed: invalid token', [
                'email' => $email,
                'user_id' => $user->id
            ]);
            throw new Exception('Invalid or expired reset token', 400);
        }

        DB::beginTransaction();
        try {
            $user->password = PasswordUtil::hash($newPassword);
            $user->save();
            PasswordReset::query()->where('email', $user->email)->delete();
            DB::commit();

            $this->logInfo('Password reset successful', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);

        } catch (Exception $e) {
            $this->logError('Password reset failed during database operation', [
                'email' => $email,
                'user_id' => $user->id
            ], $e);
            DB::rollBack();
            throw $e;
        }
    }
}
