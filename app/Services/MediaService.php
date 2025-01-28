<?php

namespace App\Services;

use App\Handlers\MediaHandler;
use App\Helpers\FileHelper;
use App\Repositories\MediaRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MediaService
{
    private MediaRepository $mediaRepository;

    private MediaHandler $mediaHandler;

    public function __construct(MediaRepository $mediaRepository, MediaHandler $mediaHandler)
    {
        $this->mediaHandler = $mediaHandler;
        $this->mediaRepository = $mediaRepository;
    }

    public function upload($files, $is_adult, $is_subscription, $is_paid, $is_author)
    {
        $originalPath = 'originals';
        $processedPath = 'processed';

        $allCreatedFiles = [];

        if (empty($files) || ! is_array($files)) {
            throw new Exception('No files provided or invalid input format.');
        }

        foreach ($files as $file) {
            try {
                if (! $file->isValid()) {
                    throw new Exception("File {$file->getClientOriginalName()} is invalid.");
                }

                $mimeType = $file->getMimeType();
                if (! $mimeType) {
                    throw new Exception("Unable to determine MIME type for file {$file->getClientOriginalName()}.");
                }

                $type = FileHelper::determineFileType($mimeType);
                if (! $type) {
                    throw new Exception("Unsupported file type: {$mimeType}.");
                }

                $options = [
                    'is_paid' => $is_paid,
                    'is_adult' => $is_adult,
                    'is_subscription' => $is_subscription,
                    'is_author' => $is_author,
                ];

                $results = $this->mediaHandler->handleFile($type, $file, $options, $originalPath, $processedPath);

                if (empty($results)) {
                    throw new Exception("Failed to process file {$file->getClientOriginalName()}.");
                }

                foreach ($results as $resultType => $path) {
                    if (! file_exists($path)) {
                        throw new Exception("Processed file not found at path {$path}.");
                    }

                    $media = $this->mediaRepository->create([
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'type' => $resultType,
                        'mime_type' => $mimeType,
                        'size' => $file->getSize(),
                        'user_id' => Auth::id(),
                    ]);

                    if (! $media) {
                        throw new Exception("Failed to save media record for file {$file->getClientOriginalName()}.");
                    }

                    $allCreatedFiles[] = [
                        'id' => $media->id,
                        'name' => $media->name,
                        'file_path' => $media->file_path,
                        'mime_type' => $media->mime_type,
                        'size' => $media->size,
                        'type' => $media->type,
                        'user_id' => $media->user_id,
                    ];
                }
            } catch (\Exception $e) {
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
