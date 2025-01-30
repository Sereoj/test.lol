<?php

namespace App\Http\Controllers;

use App\Http\Requests\Media\MediaRequest;
use App\Services\MediaService;
use Exception;
use Illuminate\Support\Facades\Cache;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function store(MediaRequest $request)
    {
        try {
            $files = $request->file('file');
            $isAdult = $request->boolean('is_adult');
            $isSubscription = $request->boolean('is_subscription');
            $isPaid = $request->boolean('is_paid');
            $isAuthor = $request->boolean('is_author');

            $cacheKey = 'media_upload_' . md5(json_encode($files));

            if (Cache::has($cacheKey)) {
                \Log::info('Отображаю кеш');
                return response()->json(Cache::get($cacheKey));
            }

            $media = $this->mediaService->upload($files, $isAdult, $isSubscription, $isPaid, $isAuthor);
            Cache::put($cacheKey, $media, now()->addMinutes(60));

            return response()->json($media, 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $cacheKey = 'media_' . $id;
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }

            $media = $this->mediaService->getMediaById($id);

            Cache::put($cacheKey, $media, now()->addMinutes(60));

            return response()->json($media);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(MediaRequest $request, $id)
    {
        try {
            $media = $this->mediaService->updateMedia($id, $request->validated());

            $cacheKey = 'media_' . $id;
            Cache::put($cacheKey, $media, now()->addMinutes(60));

            return response()->json($media);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->mediaService->deleteMedia($id);
            Cache::forget('media_' . $id);

            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
