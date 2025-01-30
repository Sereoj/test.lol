<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected string $inputPath;

    protected string $outputPath;

    protected array $filters;

    public function __construct(string $inputPath, string $outputPath, array $filters)
    {
        $this->inputPath = $inputPath;
        $this->outputPath = $outputPath;
        $this->filters = $filters;
    }

    public function handle()
    {
        $image = Image::read($this->inputPath);

        foreach ($this->filters as $filterData) {

            $filterData['filter']->apply($image, $filterData['options']);
        }

        Storage::disk('public')->put($this->outputPath, $image->encode(new WebpEncoder(quality: 65)));
    }
}
