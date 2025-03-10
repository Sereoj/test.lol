<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\Users\UserLongResource;
use App\Services\Users\UserProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserProfileController extends Controller
{
    protected UserProfileService $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    /**
     * Получить профиль пользователя.
     */
    public function show()
    {
        $user = Auth::user();
        $cacheKey = "user_profile_{$user->id}";
        $profile = Cache::get($cacheKey);

        if (! $profile) {
            $profile = new UserLongResource($this->userProfileService->getUserProfile($user->id));

            Cache::put($cacheKey, $profile, now()->addMinutes(2));
            return response()->json($profile);
        }

        return response()->json($profile);
    }

    /**
     * Обновить профиль пользователя.
     */
    public function update(UpdateUserProfileRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();
        try {
            $profile = $this->userProfileService->updateUserProfile($user->id, $data);

            if ($profile) {
                // Обновляем кеш после успешного обновления профиля
                $cacheKey = "user_profile_{$user->id}";
                Cache::put($cacheKey, $profile, now()->addMinutes(10));

                return response()->json($profile);
            }

            return response()->json(['message' => 'Unable to update profile'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating profile: '.$e->getMessage()], 500);
        }
    }
}
