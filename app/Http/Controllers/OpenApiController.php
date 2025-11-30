<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use OpenApi\Attributes as OA;

class OpenApiController extends Controller
{
            /**
     * @OA\Get(
     *     path="/api/v1/openapi.yaml",
     *     tags={"OpenApis"},
     *     summary="Yaml open api",
     *     description="Yaml open api",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/OpenApi")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
public function yaml()
    {
        $path = storage_path('api-docs/api-docs.yaml');

        if (!File::exists($path)) {
            return response()->json([
                'error' => 'OpenAPI specification not found. Please run: php artisan l5-swagger:generate'
            ], 404);
        }

        $content = File::get($path);

        return response($content)
            ->header('Content-Type', 'application/x-yaml');
    }
}
