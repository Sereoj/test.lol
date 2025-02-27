<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserProfileRequest;
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
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        // Проверка кеша перед запросом
        $cacheKey = "user_profile_{$user->id}";
        $profile = Cache::get($cacheKey);

        if (! $profile) {
            // Если профиль не найден в кеше, запросим его из базы данных
            $profile = $this->userProfileService->getUserProfile($user->id);

            if ($profile) {
                // Кешируем профиль на 10 минут
                Cache::put($cacheKey, $profile, now()->addMinutes(10));

                return response()->json($profile);
            }

            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile);
    }

    /**
     * Обновить профиль пользователя.
     */
    public function update(UpdateUserProfileRequest $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

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
