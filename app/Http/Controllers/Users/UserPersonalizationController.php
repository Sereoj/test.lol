<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserPersonalizationRequest;
use App\Services\UserPersonalizationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserPersonalizationController extends Controller
{
    protected UserPersonalizationService $userPersonalizationService;
    public function __construct(UserPersonalizationService $userPersonalizationService)
    {
        $this->userPersonalizationService = $userPersonalizationService;
    }

    public function update(UserPersonalizationRequest $request)
    {
        try {
            $user = Auth::user();
            $userPersonalization = $this->userPersonalizationService->update($user, $request->validated());
            return $this->successResponse($userPersonalization);
        }catch (\Exception $exception){
            Log::error($exception);
            return $this->errorResponse($exception->getMessage());
        }
    }
}
