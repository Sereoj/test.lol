<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\MediaRequest;
use App\Http\Resources\Media\ShortMediaResource;
use App\Services\Media\MediaService;
use Exception;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_MEDIA = 'media_';

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(MediaRequest $request)
    {
        try {
            $files = $request->file('file');

            $options = [
                'is_paid' => $request->boolean('is_paid'),
                'is_adult' => $request->boolean('is_adult'),
                'is_subscription' => $request->boolean('is_subscription'),
                'is_author' => $request->boolean('is_author'),
                'is_public' => true,
            ];

            $media = ShortMediaResource::collection($this->mediaService->upload($files, $options));

            Log::info('Media uploaded successfully', ['media_id' => $media->id ?? 'multiple']);

            return $this->successResponse($media, 201);
        } catch (Exception $e) {
            Log::error('Error uploading media: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

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

    public function destroy($id)
    {
        try {
            $this->mediaService->deleteMedia($id);

            $cacheKey = self::CACHE_KEY_MEDIA . $id;
            $this->forgetCache($cacheKey);

            Log::info('Media deleted successfully', ['media_id' => $id]);

            return $this->successResponse(null, 204);
        } catch (Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage(), ['media_id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
