<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Services\UserProfileService;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    protected UserProfileService $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    public function show()
    {
        $user = Auth::user();
        $profile = $this->userProfileService->getUserProfile($user->id);
        if ($profile) {
            return response()->json($profile);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function update(UpdateUserProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $profile = $this->userProfileService->updateUserProfile($user->id, $data);
        if ($profile) {
            return response()->json($profile);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}
