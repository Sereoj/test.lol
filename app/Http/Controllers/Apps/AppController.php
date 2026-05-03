<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\AppRequest;
use App\Http\Resources\ShortAppResource;
use App\Services\Apps\AppService;
use Exception;
use Illuminate\Support\Facades\Log;

// Контроллер для работы с приложениями
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

    // Получение списка всех приложений
    public function index()
    {
        try {
            $apps = $this->getFromCacheOrStore(self::CACHE_KEY_APPS_LIST, self::CACHE_MINUTES, function () {
                return ShortAppResource::collection($this->appService->getAllApps());
            });

            Log::info('Список приложений успешно получен');

            return $this->successResponse($apps);
        } catch (Exception $e) {
            Log::error('Не удалось получить приложения: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch apps', 500);
        }
    }

    // Создание нового приложения
    public function store(AppRequest $request)
    {
        try {
            $app = $this->appService->createApp($request->validated());

            $this->forgetCache(self::CACHE_KEY_APPS_LIST);

            Log::info('Приложение успешно создано', ['app_id' => $app->id]);

            return $this->successResponse($app,[], 201);
        } catch (Exception $e) {
            Log::error('Не удалось создать приложение: ' . $e->getMessage(), ['data' => $request->validated()]);
            return $this->errorResponse('Failed to create app', 500);
        }
    }

    // Получение информации о конкретном приложении
    public function show($id)
    {
        try {
            $cacheKey = self::CACHE_KEY_APP . $id;

            $app = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->appService->getAppById($id);
            });

            Log::info('Приложение успешно получено', ['app_id' => $id]);

            return $this->successResponse($app);
        } catch (Exception $e) {
            Log::error('Не удалось получить приложение: ' . $e->getMessage(), ['app_id' => $id]);
            return $this->errorResponse('Failed to fetch app', 500);
        }
    }

    // Обновление информации о приложении
    public function update(AppRequest $request, $id)
    {
        try {
            $this->appService->updateApp($id, $request->validated());

            $this->forgetCache([
                self::CACHE_KEY_APP . $id,
                self::CACHE_KEY_APPS_LIST
            ]);

            Log::info('Приложение успешно обновлено', ['app_id' => $id]);

            return $this->successResponse(['message' => 'App updated successfully']);
        } catch (Exception $e) {
            Log::error('Не удалось обновить приложение: ' . $e->getMessage(), ['app_id' => $id, 'data' => $request->validated()]);
            return $this->errorResponse('Failed to update app', 500);
        }
    }

    // Удаление приложения
    public function destroy($id)
    {
        try {
            $this->appService->deleteApp($id);

            $this->forgetCache([
                self::CACHE_KEY_APP . $id,
                self::CACHE_KEY_APPS_LIST
            ]);

            Log::info('Приложение успешно удалено', ['app_id' => $id]);

            return $this->successResponse(['message' => 'App deleted successfully']);
        } catch (Exception $e) {
            Log::error('Не удалось удалить приложение: ' . $e->getMessage(), ['app_id' => $id]);
            return $this->errorResponse('Failed to delete app', 500);
        }
    }
}
