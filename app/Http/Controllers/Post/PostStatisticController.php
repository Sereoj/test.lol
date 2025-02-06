<?php

namespace App\Http\Controllers\Post;

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

    public function __construct(PostStatisticsService $postStatisticsService)
    {
        $this->postStatisticsService = $postStatisticsService;
    }

    public function getPostStatistics(int $post, getPostStatisticsRequest $request)
    {
        $userId = Auth::id();

        $filters = [
            'date_range' => [
                'start_date' => Carbon::parse($request->input('date_range.start_date'))->toIso8601String(),
                'end_date' => Carbon::parse($request->input('date_range.end_date'))->toIso8601String(),
            ],
        ];

        $cacheKey = 'post_summary_'.$userId.'_'.$post.'_'.md5(json_encode($filters));

        $statistics = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($post, $filters) {
            return $this->postStatisticsService->getPostStatistics($post, $filters);
        });

        return response()->json($statistics);
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

        // Кешируем сводную статистику для заданных фильтров
        $cacheKey = 'post_stat_summary_'.$userId.'_'.md5(json_encode($filters));

        $statistics = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId, $filters) {
            return $this->postStatisticsService->getSummaryStatistics($userId, $filters);
        });

        return response()->json($statistics);
    }

    /**
     * Получить последние статистики постов.
     */
    public function recent(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->input('limit', 10);

        // Кешируем статистику последних постов
        $cacheKey = 'post_stat_recent_'.$userId.'_limit_'.$limit;

        $statistics = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId, $limit) {
            return $this->postStatisticsService->getRecentPostsStatistics($userId, $limit);
        });

        return response()->json($statistics);
    }
}
