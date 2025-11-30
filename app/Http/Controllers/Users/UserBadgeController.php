<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetActiveBadgeRequest;
use App\Http\Requests\StoreUserBadgeRequest;
use App\Http\Requests\UpdateUserBadgeRequest;
use App\Services\Users\UserBadgeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

// Контроллер для работы с наградами пользователей
class UserBadgeController extends Controller
{
    protected UserBadgeService $userBadgeService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_BADGES_ALL = 'user_badges_all';
    private const CACHE_KEY_USER_BADGE = 'user_badge_';
    private const CACHE_KEY_ACTIVE_BADGE = 'active_badge_user_';

    public function __construct(UserBadgeService $userBadgeService)
    {
        $this->userBadgeService = $userBadgeService;
    }

                                    /**
     * @OA\Delete(
     *     path="/api/v1/user-badges/{id}",
     *     tags={"Users"},
     *     summary="Delete user badge",
     *     description="Delete user badge",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Resource deleted successfully")
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
public function destroy($id)
    {
        try {
            // Удаляем награду
            $this->userBadgeService->deleteUserBadge($id);

            // Очищаем кеш
            $this->forgetCache([
                self::CACHE_KEY_USER_BADGE . $id,
                self::CACHE_KEY_USER_BADGES_ALL
            ]);

            Log::info('User badge deleted successfully', ['badge_id' => $id, 'user_id' => Auth::id()]);

            return $this->successResponse(['message' => 'Badge deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting user badge: ' . $e->getMessage(), ['badge_id' => $id, 'user_id' => Auth::id()]);
            return $this->errorResponse('Failed to delete user badge', 500);
        }
    }
}
