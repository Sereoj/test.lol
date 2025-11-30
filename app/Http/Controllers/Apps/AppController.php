<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\AppRequest;
use App\Http\Resources\ShortAppResource;
use App\Services\Apps\AppService;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

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
    /**
     * @OA\Get(
     *     path="/api/v1/apps",
     *     tags={"Apps"},
     *     summary="Get all apps",
     *     description="Get all apps",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/App")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function index()
    {
        try {
            $apps = $this->getFromCacheOrStore(self::CACHE_KEY_APPS_LIST, self::CACHE_MINUTES, function () {
                return ShortAppResource::collection($this->appService->getAllApps());
            });

            Log::info('Apps list retrieved successfully');

            return $this->successResponse($apps);
        } catch (Exception $e) {
            Log::error('Failed to fetch apps: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch apps', 500);
        }
    }

    // Создание нового приложения   
    /**
     * @OA\Post(
     *     path="/api/v1/apps",
     *     tags={"Apps"},
     *     summary="Create new app",
     *     description="Create new app",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AppRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/App")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function store(AppRequest $request)
    {
        try {
            $app = $this->appService->createApp($request->validated());

            $this->forgetCache(self::CACHE_KEY_APPS_LIST);

            Log::info('App created successfully', ['app_id' => $app->id]);

            return $this->successResponse($app,[], 201);
        } catch (Exception $e) {
            Log::error('Failed to create app: ' . $e->getMessage(), ['data' => $request->validated()]);
            return $this->errorResponse('Failed to create app', 500);
        }
    }

    // Получение информации о конкретном приложении   
    /**
     * @OA\Get(
     *     path="/api/v1/apps/{id}",
     *     tags={"Apps"},
     *     summary="Get app by ID",
     *     description="Get app by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/App")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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

    // Обновление информации о приложении   
    /**
     * @OA\Put(
     *     path="/api/v1/apps/{id}",
     *     tags={"Apps"},
     *     summary="Update app",
     *     description="Update app",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AppRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/App")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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

    // Удаление приложения   
    /**
     * @OA\Delete(
     *     path="/api/v1/apps/{id}",
     *     tags={"Apps"},
     *     summary="Delete app",
     *     description="Delete app",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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
