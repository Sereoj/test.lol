<?php

namespace App\Services\Authentication;

use App\Models\Authentication\PasswordReset;
use App\Models\Users\User;
use App\Services\Users\UserService;
use App\Utils\CodeGenerator;
use App\Utils\PasswordUtil;
use DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;
use Log;

class PasswordResetService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function sendPasswordResetEmail(string $email): bool
    {
        try {
            $locale = app()->getLocale();
            $user = User::query()->where('email', $email)->firstOrFail();
            PasswordReset::query()->where('email', $user->email)->delete();

            $token = CodeGenerator::generate(60);

            PasswordReset::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            Mail::send("emails.resets.$locale", ['token' => $token], function ($message) use ($user) {
                $message->to($user->email)->subject('Password Reset Request');
            });

            return true;
        } catch (ModelNotFoundException $e) {
            Log::warning("Попытка сброса пароля для несуществующего email: $email");
            return false;
        } catch (Exception $e) {
            Log::error("Ошибка при отправке email для сброса пароля: " . $e->getMessage());
            return false;
        }
    }

    public function resetPassword($email, $token, $newPassword): bool
    {
        $user = $this->userService->findUserByEmail($email);
        if (!$user) {
            return false;
        }

        $passwordReset = PasswordReset::query()->where('email', $user->email)
            ->where('token', $token)
            ->first();

        if(!$passwordReset)
            return false;

        DB::beginTransaction();
        try {
            $user->password = PasswordUtil::hash($newPassword);
            $user->save();
            PasswordReset::query()->where('email', $user->email)->delete();
            DB::commit();
            return true;
        }catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return false;
        }
    }
}
