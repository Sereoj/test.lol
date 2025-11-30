<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetRequest;
use App\Services\Authentication\PasswordResetService;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для сброса пароля
class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

                        /**
     * @OA\Post(
     *     path="/api/v1/auth/reset-password",
     *     tags={"PasswordResets"},
     *     summary="ResetPassword password reset",
     *     description="ResetPassword password reset",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
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
