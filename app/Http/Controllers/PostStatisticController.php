<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\Stat\PostStatSummaryRequest;
use App\Services\PostStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PostStatisticController extends Controller
{
    protected PostStatisticsService $postStatisticsService;

    public function __construct(PostStatisticsService $postStatisticsService)
    {
        $this->postStatisticsService = $postStatisticsService;
    }

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

        $statistics = $this->postStatisticsService->getSummaryStatistics($userId, $filters);

        return response()->json($statistics);
    }

    public function recent(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->input('limit', 10);
        $statistics = $this->postStatisticsService->getRecentPostsStatistics($userId, $limit);

        return response()->json($statistics);
    }
}
