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
     * Получить статистику по конкретному посту
     */
    public function getPostStatistics(int $post, getPostStatisticsRequest $request)
    {
        $userId = Auth::id();

        $filters = [
            'date_range' => [
                'start_date' => Carbon::parse($request->input('date_range.start_date'))->toIso8601String(),
                'end_date' => Carbon::parse($request->input('date_range.end_date'))->toIso8601String(),
            ],
        ];

        $cacheKey = self::CACHE_KEY_POST_SUMMARY . $userId . '_' . $post . '_' . md5(json_encode($filters));

        $statistics = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($post, $filters) {
            return $this->postStatisticsService->getPostStatistics($post, $filters);
        });

        return $this->successResponse($statistics);
    }

    /**
     * Получить сводную статистику для постов с фильтрами.
     */
    public function summary(PostStatSummaryRequest $request)
    {
        $userId = Auth::id();
        $filters = [
            'category_id' => $request->input('category_id'),
            'date_range' => [
                'start_date' => Carbon::parse($request->input('date_range.start_date'))->toIso8601String(),
                'end_date' => Carbon::parse($request->input('date_range.end_date'))->toIso8601String(),
            ],
        ];

        $cacheKey = self::CACHE_KEY_POST_STAT_SUMMARY . $userId . '_' . md5(json_encode($filters));

        $statistics = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId, $filters) {
            return $this->postStatisticsService->getSummaryStatistics($userId, $filters);
        });

        return $this->successResponse($statistics);
    }

    /**
     * Получить последние статистики постов.
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
