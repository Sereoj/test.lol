<?php

namespace App\Processors;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageProcessor
{
    public function applyWatermark(string $inputPath, string $outputPath, ?string $watermarkText = null): void
    {
        $image = Image::read(Storage::disk('public')->path($inputPath));

        if ($watermarkText) {
            $image->text($watermarkText, $image->width() / 2, $image->height() / 2, function ($font) {
                $font->filename(public_path('fonts/fredoka.ttf'));
                $font->size(36);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('middle');
            });
        }

        Storage::disk('public')->put($outputPath, $image->encode());
    }

    private function getTextBoundingBox($text, $font, $fontSize, $angle)
    {
        $bbox = imagettfbbox($fontSize, $angle, $font, $text);

        return [
            'width' => max($bbox[2], $bbox[4]) - min($bbox[0], $bbox[6]),
            'height' => max($bbox[1], $bbox[3]) - min($bbox[5], $bbox[7]),
        ];
    }

    public function applyWatermarkWithUserName(string $inputPath, string $outputPath, string $userName): void
    {
        if ($userName) {
            $userNameFontSize = 28;
            $padding = 10; // Отступ от краев
            $fontPath = public_path('fonts/pacifico.ttf');
            $angle = 0;

            // Загрузка изображения
            $image = Image::read(Storage::disk('public')->path($inputPath));
            $imageWidth = $image->width();   // 1500
            $imageHeight = $image->height(); // 1500

            // Получаем размеры текста
            $bbox = $this->getTextBoundingBox($userName, $fontPath, $userNameFontSize, $angle);
            $textWidth = $bbox['width'];   // 729
            $textHeight = $bbox['height']; // 42

            // Вычисляем координаты для текста
            $x = $imageWidth - $textWidth - $padding;  // 1500 - 729 - 10 = 761
            $y = $imageHeight - $padding - 20;              // 1500 - 10 - 20 = 1470

            // Добавление текста
            $image->text($userName, $x, $y, function ($font) use ($fontPath, $userNameFontSize, $angle) {
                $font->file($fontPath);
                $font->size($userNameFontSize);
                $font->color('#ff0000');
                $font->align('left');
                $font->valign('bottom');
                $font->angle($angle);
            });

            // Сохранение изображения
            Storage::disk('public')->put($outputPath, $image->encode());
        }
    }

    public function applyBlur(string $inputPath, string $outputPath, int $blurLevel = 80): void
    {
        $image = Image::read(Storage::disk('public')->path($inputPath));

        $image->blur($blurLevel);

        Storage::disk('public')->put($outputPath, $image->encode());
    }

    public function applyBlurWithWatermark(string $inputPath, string $outputPath, string $watermarkText, int $blurLevel = 80): void
    {
        $image = Image::read(Storage::disk('public')->path($inputPath));

        $image->blur($blurLevel);

        $image->text($watermarkText, $image->width() / 2, $image->height() / 2, function ($font) {
            $font->filename(public_path('fonts/fredoka.ttf'));
            $font->size(36);
            $font->color('#ff0000');
            $font->align('center');
            $font->valign('middle');
        });

        Storage::disk('public')->put($outputPath, $image->encode());
    }
}
