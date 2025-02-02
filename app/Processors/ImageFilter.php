<?php

namespace App\Processors;

interface ImageFilter
{
    public function apply($image, array $options = []);
}
