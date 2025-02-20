<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\AppRequest;
use App\Services\Apps\AppService;
use Illuminate\Support\Facades\Cache;

class AppController extends Controller
{
    protected AppService $appService;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    public function index()
    {
        try {
            $cacheKey = 'apps_list';
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            $apps = $this->appService->getAllApps();
            Cache::put($cacheKey, $apps, now()->addMinutes(60));

            return response()->json($apps);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch apps'], 500);
        }
    }

    public function store(AppRequest $request)
    {
        try {
            $app = $this->appService->createApp($request->validated());

            Cache::forget('apps_list');

            return response()->json($app, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create app'], 500);
        }
    }

    public function show($id)
    {
        try {
            $cacheKey = 'app_'.$id;
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            $app = $this->appService->getAppById($id);

            Cache::put($cacheKey, $app, now()->addMinutes(60));

            return response()->json($app);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch app'], 500);
        }
    }

    public function update(AppRequest $request, $id)
    {
        try {
            $this->appService->updateApp($id, $request->validated());

            Cache::forget('app_'.$id);
            Cache::forget('apps_list');

            return response()->json(['message' => 'App updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update app'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->appService->deleteApp($id);

            // Очистить кеш для конкретного приложения и списка приложений после удаления
            Cache::forget('app_'.$id);
            Cache::forget('apps_list');

            return response()->json(['message' => 'App deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete app'], 500);
        }
    }
}
