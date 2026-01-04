<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetRequest;
use App\Services\Authentication\PasswordResetService;
use Exception;
use Illuminate\Support\Facades\Log;

// Контроллер для сброса пароля
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

            return $this->successResponse(['message' => 'Password reset email sent successfully']);
        } catch (Exception $e) {
            $this->logError('Error sending password reset email', ['email' => $request->input('email')], $e);
            $statusCode = $e->getCode() ?: 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
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

            $this->passwordResetService->resetPassword($email, $token, $newPassword);

            return $this->successResponse(['message' => 'Password reset successfully']);
        } catch (Exception $e) {
            $this->logError('Error resetting password', ['email' => $request->input('email')], $e);
            $statusCode = $e->getCode() ?: 500;
            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }
}
