<?php

namespace App\Services\Media;

use App\Handlers\MediaHandler;
use App\Helpers\FileHelper;
use App\Models\Users\UserMonthlyStat;
use App\Models\Users\UserPremiumFeature;
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
    public function upload($files, $sourceFiles = [], $sourcePrices = [])
    {
        $originalPath = 'originals';
        $processedPath = 'processed';

        $allCreatedFiles = [];

        if (empty($files) || ! is_array($files)) {
            Log::error('No files provided or invalid input format.');
            throw new Exception('No files provided or invalid input format.');
        }

        // Проверяем лимиты загрузки
        $this->checkUploadLimits();

        $files = array_filter($files, function ($file) {
            return $file->isValid() && $file->getMimeType();
        });

        $fileIndex = 0;
        foreach ($files as $file) {
            try {
                $mimeType = $file->getMimeType();
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();

                // Проверяем размер файла
                $this->checkFileSize($fileSize);

                Log::info('MediaService: Processing file', [
                    'file_name' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $fileSize,
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

                // Обработка исходного файла, если он был загружен
                if (isset($sourceFiles[$fileIndex]) && $sourceFiles[$fileIndex] && $originalMedia) {
                    $sourceFile = $sourceFiles[$fileIndex];
                    $sourcePrice = $sourcePrices[$fileIndex] ?? null;

                    if ($sourceFile->isValid() && $sourcePrice !== null && $sourcePrice > 0) {
                        try {
                            Log::info('MediaService: Processing source file', [
                                'media_id' => $originalMedia->id,
                                'source_file_name' => $sourceFile->getClientOriginalName(),
                                'source_price' => $sourcePrice,
                            ]);

                            // Загружаем исходник в отдельную папку
                            $sourcePath = 'sources/' . date('Y/m');
                            $sourceFileName = uniqid() . '_' . $sourceFile->getClientOriginalName();
                            $disk = StorageService::get();

                            $uploadedSourcePath = $sourceFile->storeAs($sourcePath, $sourceFileName, $disk);

                            // Обновляем запись оригинального медиа
                            $this->mediaRepository->update($originalMedia->id, [
                                'source_file_path' => $uploadedSourcePath,
                                'source_price' => $sourcePrice,
                                'has_source' => true,
                            ]);

                            Log::info('MediaService: Source file uploaded successfully', [
                                'media_id' => $originalMedia->id,
                                'source_path' => $uploadedSourcePath,
                                'source_price' => $sourcePrice,
                            ]);
                        } catch (Exception $e) {
                            Log::error('MediaService: Error processing source file', [
                                'media_id' => $originalMedia->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }

                Cache::put($cacheKey, $mediaData, now()->addMinutes(60));
                $allCreatedFiles = array_merge($allCreatedFiles, $mediaData);

                // Инкрементируем счетчик загрузок
                $this->incrementUploadCounter();

                Log::info('MediaService: File processing completed', [
                    'file_name' => $originalName,
                    'media_count' => count($mediaData),
                ]);

                $fileIndex++;
            } catch (Exception $e) {
                Log::error("MediaService: Error processing file", [
                    'file_name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $fileIndex++;
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

    /**
     * Проверить лимиты загрузки для текущего пользователя
     */
    private function checkUploadLimits(): void
    {
        $userId = Auth::id();

        $premiumFeatures = UserPremiumFeature::where('user_id', $userId)->first();

        if (!$premiumFeatures) {
            throw new Exception('Premium features not found for user.');
        }

        $uploadLimit = $premiumFeatures->upload_limit;
        $monthlyStat = UserMonthlyStat::getOrCreateForCurrentMonth($userId);

        if ($monthlyStat->isLimitExceeded($uploadLimit)) {
            Log::warning('MediaService: Upload limit exceeded', [
                'user_id' => $userId,
                'current_uploads' => $monthlyStat->uploads_count,
                'limit' => $uploadLimit,
            ]);
            throw new Exception("Вы достигли лимита загрузок ({$uploadLimit} в месяц). Пожалуйста, оформите Premium подписку для увеличения лимита.");
        }
    }

    /**
     * Проверить размер файла
     */
    private function checkFileSize(int $fileSize): void
    {
        $userId = Auth::id();

        $premiumFeatures = UserPremiumFeature::where('user_id', $userId)->first();

        if (!$premiumFeatures) {
            throw new Exception('Premium features not found for user.');
        }

        $maxFileSizeMB = $premiumFeatures->max_file_size;
        $maxFileSizeBytes = $maxFileSizeMB * 1024 * 1024;

        if ($fileSize > $maxFileSizeBytes) {
            Log::warning('MediaService: File size exceeded', [
                'user_id' => $userId,
                'file_size' => $fileSize,
                'max_size' => $maxFileSizeBytes,
            ]);
            throw new Exception("Размер файла превышает максимально допустимый ({$maxFileSizeMB} МБ). Пожалуйста, оформите Premium подписку для увеличения лимита.");
        }
    }

    /**
     * Инкрементировать счетчик загрузок
     */
    private function incrementUploadCounter(): void
    {
        $userId = Auth::id();
        $monthlyStat = UserMonthlyStat::getOrCreateForCurrentMonth($userId);
        $monthlyStat->incrementUploads();

        Log::info('MediaService: Upload counter incremented', [
            'user_id' => $userId,
            'uploads_count' => $monthlyStat->uploads_count,
        ]);
    }
}
