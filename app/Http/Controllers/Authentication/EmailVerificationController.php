<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\Authentication\EmailVerificationService;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailVerificationController extends Controller
{
    protected EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    public function sendVerificationCode(SendVerificationCodeRequest $request)
    {
        try {
            $email = $request->input('email');
            $result = $this->emailVerificationService->sendVerificationCode($email);

            if ($result['status']) {
                Log::info('Verification code sent successfully', ['email' => $email]);
                return $this->successResponse(['message' => $result['message']]);
            } else {
                Log::warning('Failed to send verification code', ['email' => $email, 'error' => $result['error'] ?? '']);
                return $this->errorResponse($result['message'], 500);
            }
        } catch (Exception $e) {
            Log::error('Error sending verification code: ' . $e->getMessage(), ['email' => $request->input('email')]);
            return $this->errorResponse('Error sending verification code: ' . $e->getMessage(), 500);
        }
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            $email = $request->input('email');
            $code = $request->input('code');
            $result = $this->emailVerificationService->verifyEmail($email, $code);

            if ($result['status']) {
                Log::info('Email verified successfully', ['email' => $email]);
                return $this->successResponse(['message' => $result['message']]);
            } else {
                Log::warning('Failed to verify email', ['email' => $email, 'error' => $result['error'] ?? '']);
                return $this->errorResponse($result['message'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error verifying email: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'code' => $request->input('code')
            ]);
            return $this->errorResponse('Error verifying email: ' . $e->getMessage(), 500);
        }
    }
}
