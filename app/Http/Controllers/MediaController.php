<?php

namespace App\Http\Controllers;

use App\Http\Requests\Media\MediaRequest;
use App\Services\MediaService;
use Exception;

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

            $media = $this->mediaService->upload($files, $isAdult, $isSubscription, $isPaid, $isAuthor);

            return response()->json($media, 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $media = $this->mediaService->getMediaById($id);

            return response()->json($media);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(MediaRequest $request, $id)
    {
        try {
            $media = $this->mediaService->updateMedia($id, $request->validated());

            return response()->json($media);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->mediaService->deleteMedia($id);

            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
