<?php

namespace App\Http\Controllers;

use App\Http\Requests\Badge\StoreBadgeRequest;
use App\Http\Requests\Badge\UpdateBadgeRequest;
use App\Http\Resources\BadgeResource;
use App\Services\Content\BadgeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

// Контроллер для работы с бейджами
class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    private const CACHE_MINUTES_LIST = 5;
    private const CACHE_MINUTES_SINGLE = 60;
    private const CACHE_KEY_BADGES_LIST = 'badges_list';
    private const CACHE_KEY_BADGE = 'badge_';

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

                        /**
     * @OA\Delete(
     *     path="/api/v1/badges/{id}",
     *     tags={"Badges"},
     *     summary="Delete badge",
     *     description="Delete badge",
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
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function destroy($id)
    {
        $result = $this->badgeService->delete($id);

        if ($result) {
            $this->forgetCache([
                self::CACHE_KEY_BADGE . $id,
                self::CACHE_KEY_BADGES_LIST
            ]);

            return $this->successResponse(['message' => 'Badge deleted successfully']);
        }

        return $this->errorResponse('Badge not found', 404);
    }
}
