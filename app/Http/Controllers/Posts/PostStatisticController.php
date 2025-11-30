<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\getPostStatisticsRequest;
use App\Http\Requests\Post\Stat\PostStatSummaryRequest;
use App\Services\Posts\PostStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

// Контроллер для работы с статистикой постов
class PostStatisticController extends Controller
{
    protected PostStatisticsService $postStatisticsService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_POST_SUMMARY = 'post_summary_';
    private const CACHE_KEY_POST_STAT_SUMMARY = 'post_stat_summary_';
    private const CACHE_KEY_POST_STAT_RECENT = 'post_stat_recent_';

    public function __construct(PostStatisticsService $postStatisticsService)
    {
        $this->postStatisticsService = $postStatisticsService;
    }

                            /**
     * @OA\Get(
     *     path="/api/v1/posts/statistics/recent",
     *     tags={"Posts"},
     *     summary="Recent post statistic",
     *     description="Recent post statistic",
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
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
public function recent(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->input('limit', 10);

        $cacheKey = self::CACHE_KEY_POST_STAT_RECENT . $userId . '_limit_' . $limit;

        $statistics = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId, $limit) {
            return $this->postStatisticsService->getRecentPostsStatistics($userId, $limit);
        });

        return $this->successResponse($statistics);
    }
}
