<?php

namespace App\Http\Controllers;

use App\Http\Requests\Badge\StoreBadgeRequest;
use App\Http\Requests\Badge\UpdateBadgeRequest;
use App\Http\Resources\BadgeResource;
use App\Services\Content\BadgeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

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
     * Получить список всех бейджей
     */
    public function index()
    {
        $badges = $this->getFromCacheOrStore(self::CACHE_KEY_BADGES_LIST, self::CACHE_MINUTES_LIST, function () {
            return BadgeResource::collection($this->badgeService->getAll());
        });

        return $this->successResponse($badges);
    }

    /**
     * Получить бейдж по ID
     */
    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_BADGE . $id;

        $badge = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES_SINGLE, function () use ($id) {
            return new BadgeResource($this->badgeService->getById($id));
        });

        if ($badge) {
            return $this->successResponse($badge);
        }

        return $this->errorResponse('Badge not found', 404);
    }

    /**
     * Создать новый бейдж
     */
    public function store(StoreBadgeRequest $request)
    {
        $data = $request->validated();
        $badge = $this->badgeService->create($data);

        $this->forgetCache(self::CACHE_KEY_BADGES_LIST);

        return $this->successResponse($badge, 201);
    }

    /**
     * Обновить существующий бейдж
     */
    public function update(UpdateBadgeRequest $request, $id)
    {
        $data = $request->validated();
        $badge = $this->badgeService->update($id, $data);

        if ($badge) {
            $this->forgetCache([
                self::CACHE_KEY_BADGE . $id,
                self::CACHE_KEY_BADGES_LIST
            ]);

            return $this->successResponse($badge);
        }

        return $this->errorResponse('Badge not found', 404);
    }

    /**
     * Удалить бейдж
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
