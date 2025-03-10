<?php

namespace App\Http\Controllers;

use App\Http\Requests\Badge\StoreBadgeRequest;
use App\Http\Requests\Badge\UpdateBadgeRequest;
use App\Services\Content\BadgeService;
use Illuminate\Support\Facades\Cache;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    public function index()
    {
        $cacheKey = 'badges_list';
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $badges = $this->badgeService->getAllBadges();
        Cache::put($cacheKey, $badges, now()->addMinutes(5));

        return response()->json($badges);
    }

    public function show($id)
    {
        // Проверяем кеш для конкретного бейджа
        $cacheKey = 'badge_'.$id;
        if (Cache::has($cacheKey)) {
            // Возвращаем кешированные данные
            return response()->json(Cache::get($cacheKey));
        }

        // Если в кеше нет данных, загружаем из базы данных
        $badge = $this->badgeService->getBadgeById($id);

        if ($badge) {
            // Кешируем результат на 60 минут
            Cache::put($cacheKey, $badge, now()->addMinutes(60));
            return response()->json($badge);
        }

        return response()->json(['message' => 'Badge not found'], 404);
    }

    public function store(StoreBadgeRequest $request)
    {
        $data = $request->validated();
        $badge = $this->badgeService->createBadge($data);

        // Очистка кеша после добавления нового бейджа
        Cache::forget('badges_list');

        return response()->json($badge, 201);
    }

    public function update(UpdateBadgeRequest $request, $id)
    {
        $data = $request->validated();
        $badge = $this->badgeService->updateBadge($id, $data);

        if ($badge) {
            // Очистка кеша после обновления бейджа
            Cache::forget('badge_'.$id);
            Cache::forget('badges_list');

            return response()->json($badge);
        }

        return response()->json(['message' => 'Badge not found'], 404);
    }

    public function destroy($id)
    {
        $result = $this->badgeService->deleteBadge($id);

        if ($result) {
            // Очистка кеша после удаления бейджа
            Cache::forget('badge_'.$id);
            Cache::forget('badges_list');

            return response()->json(['message' => 'Badge deleted successfully']);
        }

        return response()->json(['message' => 'Badge not found'], 404);
    }
}
