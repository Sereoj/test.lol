<?php

namespace App\Processors;

class ResizeFilter implements ImageFilter
{
    public function apply($image, array $options = [])
    {
        $image->resize($options['width'] ?? 800, $options['height'] ?? 600);
    }
}
