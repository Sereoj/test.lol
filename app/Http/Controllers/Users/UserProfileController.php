<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\Users\UserLongResource;
use App\Services\Users\UserProfileService;
use App\Services\Users\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class UserProfileController extends Controller
{
    protected UserProfileService $userProfileService;
    protected UserService $userService;

    private const CACHE_MINUTES_SHOW = 2;
    private const CACHE_MINUTES_UPDATE = 10;
    private const CACHE_KEY_USER_PROFILE = 'user_profile_';

    public function __construct(UserProfileService $userProfileService, UserService $userService)
    {
        $this->userProfileService = $userProfileService;
        $this->userService = $userService;
    }

    /**
     * Получить профиль пользователя.
     */
    public function show($slug)
    {
        try {
            $user = $this->userService->getBySlug($slug);
            $cacheKey = self::CACHE_KEY_USER_PROFILE . $user->id;
            $profile = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES_SHOW, function () use ($user) {
                //return $this->userProfileService->getUserProfile($user->slug);
                return new UserProfileResource($this->userProfileService->getUserProfile($user->slug));
            });

            Log::info('User profile retrieved successfully', ['user_id' => $user->id]);

            return $this->successResponse($profile);
        } catch (Exception $e) {
            Log::error('Error retrieving user profile: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Обновить профиль пользователя.
     */
    public function update(UpdateUserProfileRequest $request)
    {
        try {
            $data = $request->validated();
            $user = Auth::user();

            $profile = $this->userProfileService->updateUserProfile($user->id, $data);

            if ($profile) {
                $cacheKey = self::CACHE_KEY_USER_PROFILE . $user->id;
                $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES_UPDATE, function () use ($profile) {
                    return $profile;
                }, true);

                Log::info('User profile updated successfully', ['user_id' => $user->id]);

                return $this->successResponse($profile);
            }

            Log::warning('Unable to update profile', ['user_id' => $user->id]);
            return $this->errorResponse('Unable to update profile', 400);
        } catch (Exception $e) {
            Log::error('Error updating user profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'data' => $request->validated()
            ]);
            return $this->errorResponse('An error occurred while updating profile: ' . $e->getMessage(), 500);
        }
    }
}
