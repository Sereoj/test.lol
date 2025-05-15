<?php

namespace App\Handlers;

use App\Processors\BlurFilter;
use App\Processors\ImagePipeline;
use App\Processors\WatermarkFilter;
use App\Processors\WatermarkWithUsername;
use App\Services\Base\AppSettingsService;
use App\Services\Media\StorageService;
use App\Services\Media\VideoService;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaHandler
{
    protected ImagePipeline $imagePipeline;

    private AppSettingsService $appSettingsService;
    private int $length;
    protected string $directoryName = 'originals';
    protected string $disk;

    public function __construct(ImagePipeline $imagePipeline, AppSettingsService $appSettingsService)
    {
        $this->imagePipeline = $imagePipeline;
        $this->appSettingsService = $appSettingsService;
        $this->length = $this->appSettingsService->get('images.length') ?? 30;

        $this->disk = StorageService::get();
        if(!Storage::drive($this->disk)->exists($this->directoryName))
        {
            Storage::disk($this->disk)->makeDirectory($this->directoryName);
        }
    }

    // Обработка медиа-файлов
    //TODO: сделать сжатие файлов, пересмотреть работу логики.
    public function handleFile(
        string $type,
        UploadedFile $file,
        string $originalPath,
        string $processedPath
    ): array {
        $results = [];
        $fileName = Str::random($this->length).'.'.$file->getClientOriginalExtension();
        //Storage::disk('ftp')->putFileAs($this->directoryName, $file, $fileName);
        $path = Storage::disk($this->disk)->putFileAs($this->directoryName, $file, $fileName);
        $results['original'] = $path;

        Log::info('File:', [
            'file' => $results['original'],
            'disk' => $this->disk,
        ]);


/*        if ($type === 'image') {
            $results = array_merge($results, $this->handleImage($file, $originalFilePath, $processedPath));
        } elseif ($type === 'gif') {
            $results = array_merge($results, $this->handleGif($file, $originalFilePath));
        } elseif ($type === 'video') {
            $results = array_merge($results, $this->handleVideo($file, $originalFilePath, $processedPath));
        }*/

        return $results;
    }

    // Обработка изображений
    protected function handleImage(UploadedFile $file, string $originalFilePath, string $processedPath): array
    {
        $results = [];

        if (! empty($options['is_paid'])) {
            $watermarkedPath = "$processedPath/watermarked/".Str::random($this->length).'.'.$file->getClientOriginalExtension();

            $pipeline = new ImagePipeline();
            $pipeline
                ->addFilter(new WatermarkFilter(), ['text' => 'Paid'])
                ->addFilter(new WatermarkWithUsername(), ['username' => '@Test'])
                ->process($originalFilePath, $watermarkedPath);

            $results['compressed'] = $watermarkedPath;
        }

        if (! empty($options['is_author'])) {
            $blurredPath = "$processedPath/watermarked/".Str::random($this->length).'.'.$file->getClientOriginalExtension();

            $pipeline = new ImagePipeline();
            $pipeline->addFilter(new WatermarkWithUsername(), ['username' => '@Test'])
                ->process($originalFilePath, $blurredPath);

            $results['compressed'] = $blurredPath;
        }

        return $results;
    }

    // Обработка GIF-файлов
    protected function handleGif(UploadedFile $file, string $originalFilePath): array
    {
        // Логика обработки GIF-файлов (например, оптимизация).
        return ['compressed' => $originalFilePath];
    }

    // Обработка видео-файлов
    protected function handleVideo(UploadedFile $file, string $originalFilePath, string $processedPath): array
    {
        $results = [];

        $storagePath = sprintf("%s/", storage_path('app/public'));

        if (!empty($options['is_paid'])) {
            $processedFileName = Str::random(20).'.mp4';
            $watermarkedPath = "$processedPath/watermarked/".$processedFileName;

            copy($originalFilePath, $storagePath.$watermarkedPath);
            $results['compressed'] = $watermarkedPath;
        }

        if (!empty($options['is_author'])) {
            $processedFileName = Str::random(20).'.mp4';
            $authorWatermarkedPath = "$processedPath/watermarked/".$processedFileName;

            copy($originalFilePath, $storagePath.$authorWatermarkedPath);
            $results['compressed'] = $authorWatermarkedPath;
        }

        return $results;
    }
}
