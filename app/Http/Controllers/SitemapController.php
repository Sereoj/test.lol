<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class SitemapController extends Controller
{
    protected SitemapService $sitemapService;
    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

                /**
     * @OA\Get(
     *     path="/api/v1/sitemap",
     *     tags={"Sitemaps"},
     *     summary="Get all sitemaps",
     *     description="Get all sitemaps",
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
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             )
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
public function index()
    {
        try {
            return $this->successResponse($this->sitemapService->generateUrls());
        } catch (\Exception $exception) {
            Log::error('Sitemap: ' . $exception->getMessage(), [
                'message' => $exception->getMessage(),
            ]);
            return $this->errorResponse($exception->getMessage());
        }
    }
}
