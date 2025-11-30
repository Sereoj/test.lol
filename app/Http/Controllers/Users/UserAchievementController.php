<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserAchievementRequest;
use App\Models\Content\Achievement;
use App\Services\Content\AchievementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

// Контроллер для работы с достижениями пользователей
class UserAchievementController extends Controller
{
    protected AchievementService $achievementService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_ACHIEVEMENTS = 'user_achievements_';

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

                    /**
     * @OA\Get(
     *     path="/api/v1/achievements",
     *     tags={"Users"},
     *     summary="Get all user achievements",
     *     description="Get all user achievements",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             )
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
public function index()
    {
        try {
            $user = Auth::user();
            $cacheKey = self::CACHE_KEY_USER_ACHIEVEMENTS . $user->id;

            $achievements = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
                return $user->achievements;
            });

            Log::info('User achievements retrieved successfully', ['user_id' => $user->id]);

            return $this->successResponse($achievements);
        } catch (Exception $e) {
            Log::error('Error retrieving user achievements: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Failed to retrieve user achievements', 500);
        }
    }

    /**
     * Добавить достижение пользователю.
     */
    public function store(StoreUserAchievementRequest $request)
    {
        try {
            $user = Auth::user();
            $achievement = Achievement::findOrFail($request->achievement_id);

            $this->achievementService->assignAchievementToUser($user, $achievement);

            // Очистка кеша достижений пользователя
            $this->forgetCache(self::CACHE_KEY_USER_ACHIEVEMENTS . $user->id);

            Log::info('Achievement assigned to user successfully', [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id
            ]);

            // Возвращаем актуальные достижения
            return $this->successResponse($user->achievements, [], 201);
        } catch (Exception $e) {
            Log::error('Error assigning achievement to user: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'achievement_id' => $request->achievement_id
            ]);
            return $this->errorResponse('Failed to assign achievement', 500);
        }
    }

    /**
     * Удалить достижение у пользователя.
     */
    public function destroy(Achievement $achievement)
    {
        try {
            $user = Auth::user();
            $this->achievementService->removeAchievementFromUser($user, $achievement);

            // Очистка кеша достижений пользователя
            $this->forgetCache(self::CACHE_KEY_USER_ACHIEVEMENTS . $user->id);

            Log::info('Achievement removed from user successfully', [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id
            ]);

            return $this->successResponse($user->achievements);
        } catch (Exception $e) {
            Log::error('Error removing achievement from user: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'achievement_id' => $achievement->id
            ]);
            return $this->errorResponse('Failed to remove achievement', 500);
        }
    }
}
