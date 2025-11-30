<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UserFollowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

// Контроллер для работы с подписками на пользователей
class UserFollowController extends Controller
{
    protected UserFollowService $followService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_FOLLOWING = 'user_%s_following_%s';
    private const CACHE_KEY_USER_FOLLOWERS = 'user_%s_followers';
    private const CACHE_KEY_USER_FOLLOWING_LIST = 'user_%s_following';

    public function __construct(UserFollowService $followService)
    {
        $this->followService = $followService;
    }

                            /**
     * @OA\Get(
     *     path="/api/v1/user/{user}/following",
     *     tags={"Users"},
     *     summary="Followers user follow",
     *     description="Followers user follow",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
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
public function followers()
    {
        try {
            $userId = Auth::id();
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWERS, $userId);

            $followers = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return $this->followService->getFollowers($userId);
            });

            Log::info('Followers retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($followers);
        } catch (Exception $e) {
            Log::error('Error retrieving followers: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while retrieving followers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Получить список пользователей, на которых подписан данный пользователь.
     */
    public function following()
    {
        try {
            $userId = Auth::id();
            $cacheKey = sprintf(self::CACHE_KEY_USER_FOLLOWING_LIST, $userId);

            $following = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () {
                return $this->followService->getFollowing();
            });

            Log::info('Following list retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($following);
        } catch (Exception $e) {
            Log::error('Error retrieving following list: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while retrieving following list: ' . $e->getMessage(), 500);
        }
    }
}
