<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Models\Posts\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class PlatformStatsController extends Controller
{
    private const CACHE_MINUTES = 15;
    private const CACHE_KEY = 'platform_stats';

    /**
     * Получить статистику платформы
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $stats = $this->getFromCacheOrStore(self::CACHE_KEY, self::CACHE_MINUTES, function () {
                return [
                    'users_count' => User::count(),
                    'posts_count' => Post::count(),
                ];
            });

            Log::info('Platform stats retrieved successfully');

            return $this->successResponse($stats);
        } catch (Exception $e) {
            Log::error('Error retrieving platform stats: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
