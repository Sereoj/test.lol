<?php

namespace App\Http\Controllers;

use App\Services\InitService;
use Illuminate\Support\Facades\Log;
use Exception;

class InitController extends Controller
{
    protected InitService $initService;
    
    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_INIT_INFO = 'init_info';
    
    public function __construct(InitService $initService)
    {
        $this->initService = $initService;
    }
    
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
