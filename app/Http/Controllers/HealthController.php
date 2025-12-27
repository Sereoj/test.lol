<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * Check the health status of all application services
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];

        // Check Database Connection
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // Check Redis Connection
        try {
            Redis::ping();
            $checks['redis'] = [
                'status' => 'ok',
                'message' => 'Redis connection successful'
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['redis'] = [
                'status' => 'error',
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }

        // Check Storage
        try {
            $storageWritable = is_writable(storage_path());
            $checks['storage'] = [
                'status' => $storageWritable ? 'ok' : 'error',
                'message' => $storageWritable ? 'Storage is writable' : 'Storage is not writable'
            ];

            if (!$storageWritable) {
                $status = 'unhealthy';
            }
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['storage'] = [
                'status' => 'error',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
        }

        // Application Info
        $info = [
            'app_name' => config('app.name'),
            'app_version' => env('APP_VERSION', 'unknown'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
        ];

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'info' => $info
        ], $status === 'healthy' ? 200 : 503);
    }
}
