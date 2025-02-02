<?php

namespace App\Processors;

use App\Jobs\ProcessImageJob;
use Queue;

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
