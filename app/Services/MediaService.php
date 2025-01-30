<?php

namespace App\Services;

use App\Handlers\MediaHandler;
use App\Helpers\FileHelper;
use App\Repositories\MediaRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
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

    public function upload($files, $is_adult, $is_subscription, $is_paid, $is_author, $is_public = true)
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

                $type = FileHelper::determineFileType($mimeType);
                if (! $type) {
                    Log::error("Unsupported file type: {$mimeType}.");

                    continue;
                }

                $options = [
                    'is_paid' => $is_paid,
                    'is_adult' => $is_adult,
                    'is_subscription' => $is_subscription,
                    'is_author' => $is_author,
                    'is_public' => $is_public,
                ];

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

                foreach ($results as $resultType => $path) {

                    $media = $this->mediaRepository->create([
                        'uuid' => Str::uuid(),
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'type' => $resultType,
                        'mime_type' => $mimeType,
                        'size' => $file->getSize(),
                        'user_id' => Auth::id(),
                        'is_public' => $is_public,
                        'width' => $width,
                        'height' => $height,
                        'parent_id' => ($resultType === 'original') ? null : $originalMedia->id,
                    ]);

                    if ($resultType === 'original') {
                        $originalMedia = $media;
                    }

                    $allCreatedFiles[] = $media->toArray();
                }
            } catch (Exception $e) {
                Log::error("Error processing file {$file->getClientOriginalName()}: {$e->getMessage()}");
            }
        }

        return $allCreatedFiles;
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
