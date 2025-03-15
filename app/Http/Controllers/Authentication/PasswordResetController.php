<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetRequest;
use App\Services\Authentication\PasswordResetService;
use Exception;

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

            return response()->json(['message' => 'Password reset email sent successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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
                return response()->json(['message' => 'Password reset successfully'], 200);
            }
            return response()->json(['message' => 'Invalid token or email'], 400);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
