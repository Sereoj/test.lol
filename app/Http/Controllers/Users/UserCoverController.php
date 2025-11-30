<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserCoverRequest;
use App\Http\Resources\Users\UserCoverResource;
use App\Services\Users\UserCoverService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserCoverController extends Controller
{
    protected UserCoverService $userCoverService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_USER_COVER = 'user_cover_';

    public function __construct(UserCoverService $userCoverService)
    {
        $this->userCoverService = $userCoverService;
    }

                    /**
     * @OA\Get(
     *     path="/api/v1/cover",
     *     tags={"Users"},
     *     summary="Get user cover by ID",
     *     description="Get user cover by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
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
public function show()
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_USER_COVER . $userId;
            
            $userCover = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                $user = Auth::user();
                return new UserCoverResource($user);
            });

            Log::info('User cover retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($userCover);
        } catch (Exception $e) {
            Log::error('Error retrieving user cover: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
} 