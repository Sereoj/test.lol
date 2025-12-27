<?php

namespace App\Services\Media;

use App\Handlers\MediaHandler;
use App\Helpers\FileHelper;
use App\Repositories\MediaRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function App\Helpers\getvideosize;

class MediaService
{
    private MediaRepository $mediaRepository;

    private MediaHandler $mediaHandler;

    public function __construct(MediaRepository $mediaRepository, MediaHandler $mediaHandler)
    {
        $this->mediaHandler = $mediaHandler;
        $this->mediaRepository = $mediaRepository;
    }

    // Загрузка медиа-файлов
    public function upload($files)
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
                $originalName = $file->getClientOriginalName();

                Log::info('MediaService: Processing file', [
                    'file_name' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $file->getSize(),
                    'user_id' => Auth::id(),
                ]);

                $cacheKey = sprintf('file_%s_%s', Auth::id(), md5($originalName));

                if (Cache::has($cacheKey)) {
                    $cachedData = Cache::get($cacheKey);
                    $allCreatedFiles = array_merge($allCreatedFiles, $cachedData);
                    Log::info('MediaService: Using cached file', [
                        'cache_key' => $cacheKey,
                        'file_name' => $originalName,
                    ]);
                    continue;
                }

                $type = FileHelper::determineFileType($mimeType);
                if (! $type) {
                    Log::error("MediaService: Unsupported file type", [
                        'file_name' => $originalName,
                        'mime_type' => $mimeType,
                    ]);
                    continue;
                }

                Log::info('MediaService: Uploading file to storage', [
                    'file_name' => $originalName,
                    'type' => $type,
                    'disk' => StorageService::get(),
                ]);

                $results = $this->mediaHandler->handleFile($type, $file, $originalPath, $processedPath);

                if (empty($results)) {
                    Log::error("MediaService: Failed to process file", [
                        'file_name' => $originalName,
                    ]);
                    continue;
                }

                Log::info('MediaService: File uploaded, creating media records', [
                    'file_name' => $originalName,
                    'results' => $results,
                ]);

                $width = null;
                $height = null;

                switch ($type) {
                    case 'image':
                        [$width, $height] = getimagesize($file);
                        break;
                    case 'video':
                        [$width, $height] = getvideosize($file);
                        break;
                    default:
                }

                $originalMedia = null;
                $mediaData = [];

                foreach ($results as $resultType => $path) {
                    $disk = StorageService::get();
                    $url = StorageService::getPath($path);

                    $media = $this->mediaRepository->create([
                        'uuid' => Str::uuid(),
                        'name' => $originalName,
                        'file_path' => $path,
                        'type' => $resultType,
                        'mime_type' => $mimeType,
                        'size' => $file->getSize(),
                        'user_id' => Auth::id(),
                        'disk' => $disk,
                        'is_public' => true,
                        'width' => $width,
                        'height' => $height,
                        'parent_id' => ($resultType === 'original') ? null : $originalMedia->id,
                    ]);

                    if ($resultType === 'original') {
                        $originalMedia = $media;
                    }

                    $mediaData[] = $media;

                    Log::info('MediaService: Media record created', [
                        'media_id' => $media->id,
                        'file_name' => $originalName,
                        'type' => $resultType,
                        'path' => $path,
                        'url' => $url,
                        'disk' => $disk,
                    ]);
                }

                Cache::put($cacheKey, $mediaData, now()->addMinutes(60));
                $allCreatedFiles = array_merge($allCreatedFiles, $mediaData);

                Log::info('MediaService: File processing completed', [
                    'file_name' => $originalName,
                    'media_count' => count($mediaData),
                ]);

            } catch (Exception $e) {
                Log::error("MediaService: Error processing file", [
                    'file_name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return collect($allCreatedFiles);
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
