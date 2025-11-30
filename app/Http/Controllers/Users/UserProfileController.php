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
use OpenApi\Attributes as OA;

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
     * @OA\Get(
     *     path="/api/v1/profile/{slug}",
     *     tags={"Users"},
     *     summary="Get user profile by ID",
     *     description="Get user profile by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserProfile")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function show($slug)
    {
        try {
            $user = $this->userService->getBySlug($slug);
            $cacheKey = self::CACHE_KEY_USER_PROFILE . $user->id;
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
