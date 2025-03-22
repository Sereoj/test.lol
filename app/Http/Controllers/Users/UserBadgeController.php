<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\SetActiveBadgeRequest;
use App\Http\Requests\StoreUserBadgeRequest;
use App\Http\Requests\UpdateUserBadgeRequest;
use App\Services\Users\UserBadgeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Cache;

class UserBadgeController extends Controller
{
    protected UserBadgeService $userBadgeService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_BADGES_ALL = 'user_badges_all';
    private const CACHE_KEY_USER_BADGE = 'user_badge_';
    private const CACHE_KEY_ACTIVE_BADGE = 'active_badge_user_';

    public function __construct(UserBadgeService $userBadgeService)
    {
        $this->userBadgeService = $userBadgeService;
    }

    /**
     * Получить все badge пользователей.
     */
    public function index()
    {
        try {
            // Кешируем список наград
            $badges = $this->getFromCacheOrStore(self::CACHE_KEY_USER_BADGES_ALL, self::CACHE_MINUTES, function () {
                return $this->userBadgeService->getAllUserBadges();
            });
            
            Log::info('All user badges retrieved successfully');
            
            return $this->successResponse($badges);
        } catch (Exception $e) {
            Log::error('Error retrieving all user badges: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve user badges', 500);
        }
    }

    /**
     * Создать badge пользователя.
     */
    public function store(StoreUserBadgeRequest $request)
    {
        try {
            $userId = Auth::id();
            // Создаем новую награду
            $badge = $this->userBadgeService->createUserBadge($request->validated() + ['user_id' => $userId]);

            // Очищаем кеш, так как награды могли измениться
            $this->forgetCache(self::CACHE_KEY_USER_BADGES_ALL);
            
            Log::info('User badge created successfully', [
                'user_id' => $userId, 
                'badge_id' => $badge->id
            ]);

            return $this->successResponse($badge, 201);
        } catch (Exception $e) {
            Log::error('Error creating user badge: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Failed to create user badge', 500);
        }
    }

    /**
     * Получить badge по ID.
     */
    public function show($id)
    {
        try {
            // Кешируем награду по ID
            $cacheKey = self::CACHE_KEY_USER_BADGE . $id;
            
            $badge = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->userBadgeService->getUserBadgeById($id);
            });
            
            Log::info('User badge retrieved successfully', ['badge_id' => $id]);
            
            return $this->successResponse($badge);
        } catch (Exception $e) {
            Log::error('Error retrieving user badge: ' . $e->getMessage(), ['badge_id' => $id, 'user_id' => Auth::id()]);
            return $this->errorResponse('Failed to retrieve user badge', 500);
        }
    }

    /**
     * Обновить badge пользователя.
     */
    public function update(UpdateUserBadgeRequest $request, $id)
    {
        try {
            $badge = $this->userBadgeService->updateUserBadge($id, $request->validated());

            $this->forgetCache([
                self::CACHE_KEY_USER_BADGE . $id,
                self::CACHE_KEY_USER_BADGES_ALL
            ]);
            
            Log::info('User badge updated successfully', ['badge_id' => $id, 'user_id' => Auth::id()]);

            return $this->successResponse($badge);
        } catch (Exception $e) {
            Log::error('Error updating user badge: ' . $e->getMessage(), ['badge_id' => $id, 'user_id' => Auth::id()]);
            return $this->errorResponse('Failed to update user badge: ' . $e->getMessage(), 500);
        }
    }

    public function setActiveBadge(SetActiveBadgeRequest $request)
    {
        try {
            $badgeId = $request->input('badge_id');
            $userId = Auth::id();
            
            $this->userBadgeService->setActiveBadgeForUser($userId, $badgeId);
            
            $this->forgetCache(self::CACHE_KEY_ACTIVE_BADGE . $userId);
            
            Log::info('Badge set as active successfully', ['badge_id' => $badgeId, 'user_id' => $userId]);

            return $this->successResponse(['message' => 'Badge set as active successfully']);
        } catch (Exception $e) {
            Log::error('Error setting active badge: ' . $e->getMessage(), [
                'badge_id' => $request->input('badge_id'), 
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function getActiveBadge()
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_ACTIVE_BADGE . $userId;
            
            $activeBadge = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                return $this->userBadgeService->getActiveBadgeForUser($userId);
            });
            
            Log::info('Active badge retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($activeBadge);
        } catch (Exception $e) {
            Log::error('Error retrieving active badge: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Удалить badge пользователя.
     */
    public function destroy($id)
    {
        try {
            // Удаляем награду
            $this->userBadgeService->deleteUserBadge($id);

            // Очищаем кеш
            $this->forgetCache([
                self::CACHE_KEY_USER_BADGE . $id,
                self::CACHE_KEY_USER_BADGES_ALL
            ]);
            
            Log::info('User badge deleted successfully', ['badge_id' => $id, 'user_id' => Auth::id()]);

            return $this->successResponse(['message' => 'Badge deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting user badge: ' . $e->getMessage(), ['badge_id' => $id, 'user_id' => Auth::id()]);
            return $this->errorResponse('Failed to delete user badge', 500);
        }
    }
}
