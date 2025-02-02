<?php

namespace App\Handlers;

use App\Processors\BlurFilter;
use App\Processors\ImagePipeline;
use App\Processors\WatermarkFilter;
use App\Processors\WatermarkWithUsername;
use App\Services\Base\AppSettingsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaHandler
{
    protected ImagePipeline $imagePipeline;

    private AppSettingsService $appSettingsService;

    private int $length;

    public function __construct(ImagePipeline $imagePipeline, AppSettingsService $appSettingsService)
    {
        $this->imagePipeline = $imagePipeline;
        $this->appSettingsService = $appSettingsService;
        $this->length = $this->appSettingsService->get('images.length');
    }

    public function handleFile(
        string $type,
        UploadedFile $file,
        array $options,
        string $originalPath,
        string $processedPath
    ): array {
        $results = [];
        $fileName = Str::random($this->length).'.'.$file->getClientOriginalExtension();
        $originalFilePath = $file->getPathname();

        $results['original'] = Storage::disk('public')->putFileAs($originalPath, $file, $fileName);

        if ($type === 'image') {
            $results = array_merge($results, $this->handleImage($file, $originalFilePath, $options, $processedPath));
        } elseif ($type === 'gif') {
            $results = array_merge($results, $this->handleGif($file, $originalFilePath));
        } elseif ($type === 'video') {
            $results = array_merge($results, $this->handleVideo($file, $originalFilePath));
        }

        return $results;
    }

    protected function handleImage(UploadedFile $file, string $originalFilePath, array $options, string $processedPath): array
    {
        $results = [];

        if (! empty($options['is_paid'])) {
            $watermarkedPath = "$processedPath/watermarked/".Str::random($this->length).'.'.$file->getClientOriginalExtension();

            $pipeline = new ImagePipeline;
            $pipeline
                ->addFilter(new WatermarkFilter, ['text' => 'Paid'])
                ->addFilter(new WatermarkWithUsername, ['username' => '@Test'])
                ->process($originalFilePath, $watermarkedPath);

            $results['compressed'] = $watermarkedPath;
        }

        if (! empty($options['is_author'])) {
            $blurredPath = "$processedPath/watermarked/".Str::random($this->length).'.'.$file->getClientOriginalExtension();

            $pipeline = new ImagePipeline;
            $pipeline->addFilter(new WatermarkWithUsername, ['username' => '@Test'])
                ->process($originalFilePath, $blurredPath);

            $results['compressed'] = $blurredPath;
        }

        if (! empty($options['is_adult'])) {
            $blurredAdultPath = "$processedPath/blurred_adult/".Str::random($this->length).'.'.$file->getClientOriginalExtension();

            $pipeline = new ImagePipeline;
            $pipeline->addFilter(new BlurFilter, ['blur' => 30])
                ->addFilter(new WatermarkFilter, ['text' => 'Content 18+'])
                ->process($originalFilePath, $blurredAdultPath);

            $results['compressed'] = $blurredAdultPath;
        }

        if (! empty($options['is_subscription'])) {
            $blurredPath = "$processedPath/blurred/".Str::random($this->length).'.'.$file->getClientOriginalExtension();

            $pipeline = new ImagePipeline;
            $pipeline->addFilter(new BlurFilter, ['blur' => 30])
                ->addFilter(new WatermarkFilter, ['text' => 'Водяной знак'])
                ->process($originalFilePath, $blurredPath);

            $results['compressed'] = $blurredPath;
        }

        return $results;
    }

    protected function handleGif(UploadedFile $file, string $originalFilePath): array
    {
        // Логика обработки GIF-файлов (например, оптимизация).
        return ['compressed' => $originalFilePath];
    }

    protected function handleVideo(UploadedFile $file, string $originalFilePath): array
    {
        // Логика обработки видео (например, сжатие или изменение формата).
        return ['compressed' => $originalFilePath];
    }
}
