<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetRequest;
use App\Services\Authentication\PasswordResetService;
use Exception;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Send a password reset email to the user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPasswordResetEmail(SendPasswordResetRequest $request)
    {
        try {
            $email = $request->input('email');
            $this->passwordResetService->sendPasswordResetEmail($email);
            
            Log::info('Password reset email sent successfully', ['email' => $email]);

            return $this->successResponse(['message' => 'Password reset email sent successfully']);
        } catch (Exception $e) {
            Log::error('Error sending password reset email: ' . $e->getMessage(), ['email' => $request->input('email')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Reset the user's password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $email = $request->input('email');
            $token = $request->input('token');
            $newPassword = $request->input('new_password');

            if ($this->passwordResetService->resetPassword($email, $token, $newPassword)) {
                Log::info('Password reset successfully', ['email' => $email]);
                return $this->successResponse(['message' => 'Password reset successfully']);
            }
            
            Log::warning('Invalid token or email for password reset', ['email' => $email]);
            return $this->errorResponse('Invalid token or email', 400);
        } catch (Exception $e) {
            Log::error('Error resetting password: ' . $e->getMessage(), ['email' => $request->input('email')]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
