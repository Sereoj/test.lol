<?php

namespace App\Processors;

use Illuminate\Support\Facades\Cache;

class WatermarkWithUsername implements ImageFilter
{
    protected static ?string $fontPath = null;

    public function __construct()
    {
        if (is_null(self::$fontPath)) {
            self::$fontPath = Cache::rememberForever('pacifico_font', fn () => public_path('fonts/pacifico.ttf'));
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
            $username = mb_substr($username, 0, max(5, mb_strlen($username) - 3)).'...';
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
