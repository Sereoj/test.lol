<?php

namespace App\Http\Controllers;

use App\Http\Requests\Badge\StoreBadgeRequest;
use App\Http\Requests\Badge\UpdateBadgeRequest;
use App\Services\Content\BadgeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    private const CACHE_MINUTES_LIST = 5;
    private const CACHE_MINUTES_SINGLE = 60;
    private const CACHE_KEY_BADGES_LIST = 'badges_list';
    private const CACHE_KEY_BADGE = 'badge_';
    private const CACHE_KEY_ACTIVE_BADGE = 'active_badge_';

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
            return $this->badgeService->getAllBadges();
        });

        return $this->successResponse($badges);
    }

    /**
     * Получить бейдж по ID
     * @throws \Exception
     */
    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_BADGE . $id;

        $badge = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES_SINGLE, function () use ($id) {
            return $this->badgeService->getBadgeById($id);
        });

        if ($badge) {
            return $this->successResponse($badge);
        }

        return $this->errorResponse('Badge not found', 404);
    }

    /**
     * Создать новый бейдж
     * @throws \Exception
     */
    public function store(StoreBadgeRequest $request)
    {
        $data = $request->validated();
        $badge = $this->badgeService->createBadge($data);

        $this->forgetCache(self::CACHE_KEY_BADGES_LIST);

        return $this->successResponse($badge, 201);
    }

    /**
     * Обновить существующий бейдж
     * @throws \Exception
     */
    public function update(UpdateBadgeRequest $request, $id)
    {
        $data = $request->validated();
        $badge = $this->badgeService->updateBadge($id, $data);

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
     * @throws \Exception
     */
    public function destroy($id)
    {
        $result = $this->badgeService->deleteBadge($id);

        if ($result) {
            $this->forgetCache([
                self::CACHE_KEY_BADGE . $id,
                self::CACHE_KEY_BADGES_LIST
            ]);

            return $this->successResponse(['message' => 'Badge deleted successfully']);
        }

        return $this->errorResponse('Badge not found', 404);
    }

    /**
     * Получить активный бейдж пользователя
     */
    public function getActiveBadge()
    {
        $userId = auth()->id();
        $cacheKey = self::CACHE_KEY_ACTIVE_BADGE . $userId;

        $badge = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES_SINGLE, function () use ($userId) {
            return $this->badgeService->getActiveBadge($userId);
        });

        return $this->successResponse($badge);
    }

    /**
     * Установить активный бейдж для пользователя
     */
    public function setActiveBadge(Request $request)
    {
        $userId = auth()->id();
        $badgeId = $request->input('badge_id');

        $result = $this->badgeService->setActiveBadge($userId, $badgeId);
        $this->forgetCache(self::CACHE_KEY_ACTIVE_BADGE . $userId);

        return $this->successResponse(['message' => 'Active badge set successfully']);
    }
}
