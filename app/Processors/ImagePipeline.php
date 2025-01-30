<?php

namespace App\Processors;

use App\Jobs\ProcessImageJob;
use Illuminate\Support\Facades\Cache;
use Queue;

interface ImageFilter
{
    public function apply($image, array $options = []);
}
class WatermarkFilter implements ImageFilter
{
    protected static ?string $fontPath = null;

    public function __construct()
    {
        if (is_null(self::$fontPath)) {
            self::$fontPath = Cache::remember('main_font', 3600, function () {
                return public_path('fonts/fredoka.ttf');
            });
        }
    }

    public function apply($image, array $options = [])
    {
        $text = $options['text'] ?? 'Wallone';
        $image->text($text, $image->width() / 2, $image->height() / 2, function ($font) {
            $font->file(self::$fontPath);
            $font->size(28);
            $font->color('#ffffff');
            $font->align('center');
            $font->valign('middle');
        });
    }
}

class WatermarkWithUsername implements ImageFilter
{
    protected static ?string $fontPath = null;

    public function __construct()
    {
        if (is_null(self::$fontPath)) {
            self::$fontPath = Cache::rememberForever('pacifico_font', fn() => public_path('fonts/pacifico.ttf'));
        }
    }

    public function apply($image, array $options = [])
    {
        $username = $options['username'] ?? 'User';
        $padding = 10;
        $fontSize = 28;

        $imageWidth = $image->width();
        $imageHeight = $image->height();
        $bbox = $this->getTextBoundingBox($username, self::$fontPath, $fontSize, 0);

        // Корректируем, если текст выходит за границы
        if ($bbox['width'] > $imageWidth - 2 * $padding) {
            $username = mb_substr($username, 0, max(5, mb_strlen($username) - 3)) . '...';
            $bbox = $this->getTextBoundingBox($username, self::$fontPath, $fontSize, 0);
        }

        // Координаты для текста (правый нижний угол)
        $x = $imageWidth - $bbox['width'] - $padding;
        $y = $imageHeight - $padding;

        $image->text($username, $x, $y, function ($font) {
            $font->file(self::$fontPath);
            $font->size(28);
            $font->color('#ff0000');
            $font->align('left');
            $font->valign('bottom');
            $font->angle(0);
        });

    }

    private function getTextBoundingBox($text, $font, $fontSize, $angle)
    {
        $bbox = imagettfbbox($fontSize, $angle, $font, $text);

        return [
            'width' => abs($bbox[2] - $bbox[0]),
            'height' => abs($bbox[5] - $bbox[1]),
        ];
    }
}

class BlurFilter implements ImageFilter
{
    public function apply($image, array $options = [])
    {
        $image->blur($options['blur'] ?? 30);
    }
}

class ResizeFilter implements ImageFilter
{
    public function apply($image, array $options = [])
    {
        $image->resize($options['width'] ?? 800, $options['height'] ?? 600);
    }
}

class ImagePipeline
{
    private array $filters = [];

    public function __construct()
    {
        if (extension_loaded('imagick')) {
            \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_THREAD, 6);
        }
    }

    public function addFilter(ImageFilter $filter, array $options = []): self
    {
        $this->filters[] = ['filter' => $filter, 'options' => $options];

        return $this;
    }

    public function process(string $inputPath, string $outputPath): void
    {
        Queue::push(new ProcessImageJob($inputPath, $outputPath, $this->filters));
    }
}
