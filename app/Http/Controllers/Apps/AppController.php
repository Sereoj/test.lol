<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\AppRequest;
use App\Services\Apps\AppService;
use Illuminate\Support\Facades\Log;
use Exception;

class AppController extends Controller
{
    protected AppService $appService;
    
    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_APPS_LIST = 'apps_list';
    private const CACHE_KEY_APP = 'app_';

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    public function index()
    {
        try {
            $apps = $this->getFromCacheOrStore(self::CACHE_KEY_APPS_LIST, self::CACHE_MINUTES, function () {
                return $this->appService->getAllApps();
            });
            
            Log::info('Apps list retrieved successfully');
            
            return $this->successResponse($apps);
        } catch (Exception $e) {
            Log::error('Failed to fetch apps: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch apps', 500);
        }
    }

    public function store(AppRequest $request)
    {
        try {
            $app = $this->appService->createApp($request->validated());
            
            $this->forgetCache(self::CACHE_KEY_APPS_LIST);
            
            Log::info('App created successfully', ['app_id' => $app->id]);
            
            return $this->successResponse($app, 201);
        } catch (Exception $e) {
            Log::error('Failed to create app: ' . $e->getMessage(), ['data' => $request->validated()]);
            return $this->errorResponse('Failed to create app', 500);
        }
    }

    public function show($id)
    {
        try {
            $cacheKey = self::CACHE_KEY_APP . $id;
            
            $app = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->appService->getAppById($id);
            });
            
            Log::info('App retrieved successfully', ['app_id' => $id]);
            
            return $this->successResponse($app);
        } catch (Exception $e) {
            Log::error('Failed to fetch app: ' . $e->getMessage(), ['app_id' => $id]);
            return $this->errorResponse('Failed to fetch app', 500);
        }
    }

    public function update(AppRequest $request, $id)
    {
        try {
            $this->appService->updateApp($id, $request->validated());
            
            $this->forgetCache([
                self::CACHE_KEY_APP . $id,
                self::CACHE_KEY_APPS_LIST
            ]);
            
            Log::info('App updated successfully', ['app_id' => $id]);
            
            return $this->successResponse(['message' => 'App updated successfully']);
        } catch (Exception $e) {
            Log::error('Failed to update app: ' . $e->getMessage(), ['app_id' => $id, 'data' => $request->validated()]);
            return $this->errorResponse('Failed to update app', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->appService->deleteApp($id);
            
            $this->forgetCache([
                self::CACHE_KEY_APP . $id,
                self::CACHE_KEY_APPS_LIST
            ]);
            
            Log::info('App deleted successfully', ['app_id' => $id]);
            
            return $this->successResponse(['message' => 'App deleted successfully']);
        } catch (Exception $e) {
            Log::error('Failed to delete app: ' . $e->getMessage(), ['app_id' => $id]);
            return $this->errorResponse('Failed to delete app', 500);
        }
    }
}
