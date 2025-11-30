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
     *             @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
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
