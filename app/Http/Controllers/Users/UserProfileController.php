<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\Users\UserLongResource;
use App\Services\Users\UserProfileService;
use App\Services\Users\UserService;
use Illuminate\Support\Facades\Auth;
use Exception;

// Контроллер для работы с профилем пользователя
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

            if (!$user) {
                $this->logError('User not found', ['slug' => $slug]);
                return $this->errorResponse('Пользователь не найден', 404);
            }

            $cacheKey = self::CACHE_KEY_USER_PROFILE . $user->id;
            $profile = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES_SHOW, function () use ($user) {
                $this->logInfo('User profile retrieved successfully', ['user_id' => $user->id]);
                return new UserProfileResource($this->userProfileService->getUserProfile($user));
            });

            return $this->successResponse($profile);
        } catch (Exception $e) {
            $this->logError('Error retrieving user profile', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'auth_user_id' => Auth::id()
            ], $e);
            return $this->errorResponse('Ошибка при получении профиля пользователя', 500);
        }
    }
}
