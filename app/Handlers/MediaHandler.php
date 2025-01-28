<?php

namespace App\Handlers;

use App\Processors\ImageProcessor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaHandler
{
    protected ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function handleFile(
        string $type,
        object $file,
        array $options,
        string $originalPath,
        string $processedPath
    ): array {
        $results = [];

        $fileName = Str::random(30).'.'.$file->getClientOriginalExtension();
        $originalFilePath = Storage::disk('public')->putFileAs($originalPath, $file, $fileName);
        $results['original'] = $originalFilePath;

        Log::info($type);

        if ($type === 'image') {
            $results = array_merge($results, $this->handleImage($file, $originalFilePath, $options, $processedPath));
        } elseif ($type === 'gif') {
            $results = array_merge($results, $this->handleGif($file, $originalFilePath));
        } elseif ($type === 'video') {
            $results = array_merge($results, $this->handleVideo($file, $originalFilePath));
        }

        return $results;
    }

    protected function handleImage(object $file, string $originalFilePath, array $options, string $processedPath): array
    {
        $results = [];

        if (! empty($options['is_paid'])) {
            $watermarkedPath = "$processedPath/watermarked/".Str::random(30).'.'.$file->getClientOriginalExtension();
            $this->imageProcessor->applyWatermark($originalFilePath, $watermarkedPath, 'Водяной знак');
            $results['compressed'] = $watermarkedPath;
        }

        if (! empty($options['is_author'])) {
            $blurredPath = "$processedPath/watermarked/".Str::random(30).'.'.$file->getClientOriginalExtension();
            $this->imageProcessor->applyWatermarkWithUserName($originalFilePath, $blurredPath, '@Test');
            $results['compressed'] = $blurredPath;
        }

        if (! empty($options['is_adult'])) {
            $blurredAdultPath = "$processedPath/blurred_adult/".Str::random(30).'.'.$file->getClientOriginalExtension();
            $this->imageProcessor->applyBlurWithWatermark($originalFilePath, $blurredAdultPath, 'Content 18+');
            $results['compressed'] = $blurredAdultPath;
        }

        if (! empty($options['is_subscription'])) {
            $blurredPath = "$processedPath/blurred/".Str::random(30).'.'.$file->getClientOriginalExtension();
            $this->imageProcessor->applyBlur($originalFilePath, $blurredPath);
            $results['compressed'] = $blurredPath;
        }

        return $results;
    }

    protected function handleGif(object $file, string $originalFilePath): array
    {
        // Логика обработки GIF-файлов (например, оптимизация).
        return ['compressed' => $originalFilePath];
    }

    protected function handleVideo(object $file, string $originalFilePath): array
    {
        // Логика обработки видео (например, сжатие или изменение формата).
        return ['compressed' => $originalFilePath];
    }
}
