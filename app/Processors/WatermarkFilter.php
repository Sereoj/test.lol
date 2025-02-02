<?php

namespace App\Processors;

use Illuminate\Support\Facades\Cache;

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
