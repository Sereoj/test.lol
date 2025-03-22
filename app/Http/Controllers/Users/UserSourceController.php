<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Source\AddSourceRequest;
use App\Http\Requests\Source\RemoveSourceRequest;
use App\Services\Users\UserSourceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class UserSourceController extends Controller
{
    protected UserSourceService $userSourceService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_SOURCES = 'user_sources_';

    public function __construct(UserSourceService $userSourceService)
    {
        $this->userSourceService = $userSourceService;
    }

    /**
     * Добавить источник пользователю.
     */
    public function addSource(AddSourceRequest $request)
    {
        try {
            $user = Auth::user();

            $this->userSourceService->addSourceToUser($user, $request->input('source_id'));
            
            $this->forgetCache(self::CACHE_KEY_USER_SOURCES . $user->id);
            
            Log::info('Source added successfully', [
                'user_id' => $user->id, 
                'source_id' => $request->input('source_id')
            ]);

            return $this->successResponse(['message' => 'Source added successfully']);
        } catch (ModelNotFoundException $e) {
            Log::warning('Source not found', [
                'user_id' => Auth::id(), 
                'source_id' => $request->input('source_id')
            ]);
            return $this->errorResponse('Source not found', 404);
        } catch (Exception $e) {
            Log::error('An error occurred while adding source: ' . $e->getMessage(), [
                'user_id' => Auth::id(), 
                'source_id' => $request->input('source_id')
            ]);
            return $this->errorResponse('An error occurred while adding source: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Удалить источник у пользователя.
     */
    public function removeSource(RemoveSourceRequest $request)
    {
        try {
            $user = Auth::user();
            $this->userSourceService->removeSourceFromUser($user->id, $request->input('source_id'));
            
            $this->forgetCache(self::CACHE_KEY_USER_SOURCES . $user->id);
            
            Log::info('Source removed successfully', [
                'user_id' => $user->id, 
                'source_id' => $request->input('source_id')
            ]);

            return $this->successResponse(['message' => 'Source removed successfully']);
        } catch (ModelNotFoundException $e) {
            Log::warning('Source not found', [
                'user_id' => Auth::id(), 
                'source_id' => $request->input('source_id')
            ]);
            return $this->errorResponse('Source not found', 404);
        } catch (Exception $e) {
            Log::error('An error occurred while removing source: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'source_id' => $request->input('source_id')
            ]);
            return $this->errorResponse('An error occurred while removing source: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Получить источники пользователя.
     */
    public function getUserSources()
    {
        try {
            $user = Auth::user();
            $cacheKey = self::CACHE_KEY_USER_SOURCES . $user->id;

            $sources = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
                return $this->userSourceService->getUserSources($user->id);
            });
            
            Log::info('User sources retrieved successfully', ['user_id' => $user->id]);

            return $this->successResponse($sources);
        } catch (Exception $e) {
            Log::error('An error occurred while retrieving sources: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('An error occurred while retrieving sources: ' . $e->getMessage(), 500);
        }
    }
}
