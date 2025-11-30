<?php

namespace App\Http\Controllers;
use App\Services\InitService;
use Illuminate\Support\Facades\Log;
use Exception;
use OpenApi\Attributes as OA;

// Контроллер для инициализации приложения  
class InitController extends Controller
{
    protected InitService $initService;
    
    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_INIT_INFO = 'init_info';
    
    public function __construct(InitService $initService)
    {
        $this->initService = $initService;
    }

    // Инициализация приложения   
    /**
     * @OA\Get(
     *     path="/api/v1/init",
     *     tags={"Inits"},
     *     summary="Init init",
     *     description="Init init",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Init")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function init()
    {
        try {
            $info = $this->getFromCacheOrStore(self::CACHE_KEY_INIT_INFO, self::CACHE_MINUTES, function () {
                return $this->initService->getInfo();
            });
            
            Log::info('Init info retrieved successfully');
            
            return $this->successResponse($info);
        } catch (Exception $e) {
            Log::error('Error retrieving init info: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
