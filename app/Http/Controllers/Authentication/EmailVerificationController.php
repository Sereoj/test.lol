<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\Authentication\EmailVerificationService;

class EmailVerificationController extends Controller
{
    protected EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    public function sendVerificationCode(SendVerificationCodeRequest $request)
    {
        $result = $this->emailVerificationService->sendVerificationCode($request->input('email'));

        if ($result['status']) {
            return response()->json(['message' => $result['message']], 200);
        } else {
            return response()->json(['message' => $result['message'], 'error' => $result['error'] ?? ''], 500);
        }
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $result = $this->emailVerificationService->verifyEmail($request->input('email'), $request->input('code'));

        if ($result['status']) {
            return response()->json(['message' => $result['message']], 200);
        } else {
            return response()->json(['message' => $result['message'], 'error' => $result['error'] ?? ''], 500);
        }
    }
}
