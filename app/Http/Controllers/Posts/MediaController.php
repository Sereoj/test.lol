<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\MediaRequest;
use App\Http\Resources\Media\MediaResource;
use App\Services\Media\MediaService;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с медиа-файлами
class MediaController extends Controller
{
    protected MediaService $mediaService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_MEDIA = 'media_';

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

                            /**
     * @OA\Delete(
     *     path="/api/v1/media/{id}",
     *     tags={"Media"},
     *     summary="Delete media",
     *     description="Delete media",
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
            $this->mediaService->deleteMedia($id);

            $cacheKey = self::CACHE_KEY_MEDIA . $id;
            $this->forgetCache($cacheKey);

            Log::info('Media deleted successfully', ['media_id' => $id]);

            return $this->successResponse(null, [], 204);
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage(), ['media_id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
