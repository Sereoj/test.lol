<?php

namespace App\Services;

use App\Handlers\MediaHandler;
use App\Helpers\FileHelper;
use App\Repositories\MediaRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaService
{
    private MediaRepository $mediaRepository;

    private MediaHandler $mediaHandler;

    public function __construct(MediaRepository $mediaRepository, MediaHandler $mediaHandler)
    {
        $this->mediaHandler = $mediaHandler;
        $this->mediaRepository = $mediaRepository;
    }

    public function upload($files, array $options)
    {
        $originalPath = 'originals';
        $processedPath = 'processed';

        $allCreatedFiles = [];

        if (empty($files) || ! is_array($files)) {
            Log::error('No files provided or invalid input format.');
            throw new Exception('No files provided or invalid input format.');
        }

        $files = array_filter($files, function ($file) {
            return $file->isValid() && $file->getMimeType();
        });

        foreach ($files as $file) {
            try {
                $mimeType = $file->getMimeType();

                $cacheKey = sprintf('file_%s_%s', Auth::id(), md5($file->getClientOriginalName()));
                Log::info($cacheKey);

                if (Cache::has($cacheKey)) {
                    $cachedData = Cache::get($cacheKey);
                    $allCreatedFiles = array_merge($allCreatedFiles, $cachedData);
                    Log::info('Отображаю кеш '.$cacheKey);

                    continue;
                }

                $type = FileHelper::determineFileType($mimeType);
                if (! $type) {
                    Log::error("Unsupported file type: {$mimeType}.");

                    continue;
                }

                $results = $this->mediaHandler->handleFile($type, $file, $options, $originalPath, $processedPath);

                if (empty($results)) {
                    Log::error("Failed to process file {$file->getClientOriginalName()}.");

                    continue;
                }

                $width = null;
                $height = null;

                if ($type == 'image') {
                    [$width, $height] = getimagesize($file);
                }

                $originalMedia = null;
                $mediaData = [];

                foreach ($results as $resultType => $path) {
                    $media = $this->mediaRepository->create([
                        'uuid' => Str::uuid(),
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'type' => $resultType,
                        'mime_type' => $mimeType,
                        'size' => $file->getSize(),
                        'user_id' => Auth::id(),
                        'is_public' => $options['is_public'],
                        'width' => $width,
                        'height' => $height,
                        'parent_id' => ($resultType === 'original') ? null : $originalMedia->id,
                    ]);

                    if ($resultType === 'original') {
                        $originalMedia = $media;
                    }

                    $mediaData[] = $media->toArray();
                }

                Cache::put($cacheKey, $mediaData, now()->addMinutes(60));
                $allCreatedFiles = array_merge($allCreatedFiles, $mediaData);

            } catch (Exception $e) {
                Log::error("Error processing file {$file->getClientOriginalName()}: {$e->getMessage()}");
            }
        }

        return $allCreatedFiles ?? [];
    }

    public function getMediaById($id)
    {
        return $this->mediaRepository->getById($id);
    }

    public function updateMedia($id, array $data)
    {
        return $this->mediaRepository->update($id, $data);
    }

    public function deleteMedia($id)
    {
        return $this->mediaRepository->delete($id);
    }
}
