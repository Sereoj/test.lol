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
            $cacheKey = self::CACHE_KEY_USER_PROFILE . $user?->id ?? rand(0,10000);
            $profile = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES_SHOW, function () use ($user) {
                if($this->userProfileService->checkUser($user))
                {
                    Log::info('User profile retrieved successfully', ['user_id' => $user->id]);
                    return new UserProfileResource($this->userProfileService->getUserProfile($user->slug));
                }
                throw new Exception("User profile not found");
            });

            return $this->successResponse($profile);
        } catch (Exception $e) {
            Log::error('Error retrieving user profile: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage());
        }
    }
}
