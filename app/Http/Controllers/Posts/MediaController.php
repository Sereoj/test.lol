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
     * Загрузка медиа-файлов
     *
     * @OA\Post(
     *     path="/api/v1/media",
     *     tags={"Media"},
     *     summary="Upload media files",
     *     description="Upload one or more media files",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Media files to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Media uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Media")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(MediaRequest $request)
    {
        try {
            $files = $request->file('file');

            $media = $this->mediaService->upload($files);

            Log::info('Media', [
                'data' => $media
            ]);

            // Преобразуем каждый элемент коллекции в ресурс по отдельности
            $mediaResources = $media->map(function ($item) {
                return new MediaResource($item);
            });

            return $this->successResponse($mediaResources, [], 201);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Получение медиа-файлов   
    /**
     * @OA\Get(
     *     path="/api/v1/media/{id}",
     *     tags={"Media"},
     *     summary="Get media by ID",
     *     description="Get media by ID",
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
     *             @OA\Property(property="data", ref="#/components/schemas/Media")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function show($id)
    {
        try {
            $cacheKey = self::CACHE_KEY_MEDIA . $id;

            $media = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->mediaService->getMediaById($id);
            });

            Log::info('Media retrieved successfully', ['media_id' => $id]);

            return $this->successResponse($media);
        } catch (Exception $e) {
            Log::error('Error retrieving media: ' . $e->getMessage(), ['media_id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Обновление медиа-файлов   
    /**
     * @OA\Put(
     *     path="/api/v1/media/{id}",
     *     tags={"Media"},
     *     summary="Update media",
     *     description="Update media",
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
     *         @OA\JsonContent(ref="#/components/schemas/MediaRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Media")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function update(MediaRequest $request, $id)
    {
        try {
            $media = $this->mediaService->updateMedia($id, $request->validated());

            $cacheKey = self::CACHE_KEY_MEDIA . $id;
            $this->forgetCache($cacheKey);

            Log::info('Media updated successfully', ['media_id' => $id]);

            return $this->successResponse($media);
        } catch (Exception $e) {
            Log::error('Error updating media: ' . $e->getMessage(), ['media_id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Удаление медиа-файлов   
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
