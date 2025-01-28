<?php

namespace App\Services;

use App\Models\EmailVerification;
use App\Utils\CodeGenerator;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function sendVerificationCode(string $email): array
    {
        $locale = app()->getLocale();
        try {
            $user = $this->userService->findUserByEmail($email);

            if (! $user) {
                return ['status' => false, 'message' => 'User not found'];
            }

            $code = CodeGenerator::generate(6);
            $expiresAt = Carbon::now()->addMinutes(15); // Код действует 15 минут

            EmailVerification::query()->create([
                'user_id' => $user->id,
                'code' => $code,
                'type' => 'email',
                'expires_at' => $expiresAt,
            ]);

            Mail::send('emails.verification.'.$locale, ['code' => $code, 'username' => $user->username], function ($message) use ($user) {
                $message->to($user->email)->subject('Email Verification Code');
            });

            return ['status' => true, 'message' => 'Verification code sent'];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while sending the verification code', 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify the email using the provided code.
     */
    public function verifyEmail(string $email, string $code): array
    {
        try {
            $user = $this->userService->findUserByEmail($email);

            if (! $user) {
                return ['status' => false, 'message' => 'User not found'];
            }

            $verification = EmailVerification::query()->where('user_id', $user->id)
                ->where('code', $code)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (! $verification) {
                return ['status' => false, 'message' => 'Invalid or expired code'];
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            $verification->delete();

            return ['status' => true, 'message' => 'Email verified successfully'];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'An error occurred while verifying the email', 'error' => $e->getMessage()];
        }
    }
}
