<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\Authentication\EmailVerificationService;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

class EmailVerificationController extends Controller
{
    protected EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    // Отправка кода подтверждения на email   
    
    /**
     * @OA\Post(
     *     path="/api/v1/verify-email",
     *     tags={"EmailVerifications"},
     *     summary="VerifyEmail email verification",
     *     description="VerifyEmail email verification",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/VerifyEmailRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Email verified successfully")
     *             )
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
