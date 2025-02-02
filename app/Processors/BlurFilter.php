<?php

namespace App\Processors;

class BlurFilter implements ImageFilter
{
    public function apply($image, array $options = [])
    {
        $image->blur($options['blur'] ?? 30);
    }
}
