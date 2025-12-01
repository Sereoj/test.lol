<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserCoverRequest;
use App\Http\Resources\Users\UserCoverResource;
use App\Services\Users\UserCoverService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserCoverController extends Controller
{
    protected UserCoverService $userCoverService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_USER_COVER = 'user_cover_';

    public function __construct(UserCoverService $userCoverService)
    {
        $this->userCoverService = $userCoverService;
    }

    /**
     * Загрузить новую обложку для пользователя
     */
    public function upload(UpdateUserCoverRequest $request)
    {
        try {
            $userId = Auth::id();
            $coverFile = $request->file('cover');
            
            $user = $this->userCoverService->uploadCover($userId, $coverFile);
            
            // Обновляем кэш
            $cacheKey = self::CACHE_KEY_USER_COVER . $userId;
            $this->forgetCache($cacheKey);
            
            Log::info('User cover uploaded successfully', [
                'user_id' => $userId,
                'file_name' => $coverFile->getClientOriginalName(),
                'file_size' => $coverFile->getSize()
            ]);

            return $this->successResponse([
                'message' => 'Обложка успешно загружена',
                'user' => new UserCoverResource($user)
            ]);
        } catch (Exception $e) {
            Log::error('Error uploading user cover: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Удалить текущую обложку пользователя
     */
    public function remove()
    {
        try {
            $userId = Auth::id();
            
            $user = $this->userCoverService->removeCover($userId);
            
            // Обновляем кэш
            $cacheKey = self::CACHE_KEY_USER_COVER . $userId;
            $this->forgetCache($cacheKey);
            
            Log::info('User cover removed successfully', ['user_id' => $userId]);

            return $this->successResponse([
                'message' => 'Обложка успешно удалена',
                'user' => new UserCoverResource($user)
            ]);
        } catch (Exception $e) {
            Log::error('Error removing user cover: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить текущую обложку пользователя
     */
    public function show()
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_USER_COVER . $userId;
            
            $userCover = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($userId) {
                $user = Auth::user();
                return new UserCoverResource($user);
            });

            Log::info('User cover retrieved successfully', ['user_id' => $userId]);

            return $this->successResponse($userCover);
        } catch (Exception $e) {
            Log::error('Error retrieving user cover: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
} 