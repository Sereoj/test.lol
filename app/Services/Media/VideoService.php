<?php

namespace App\Services\Media;

use FFMpeg\FFMpeg;
use Exception;
use Illuminate\Support\Facades\Log;

class VideoService
{
    protected static $paths = [
        'Windows' => [
            'ffmpeg'  => 'C:\ffmpeg\bin\ffmpeg.exe',
            'ffprobe' => 'C:\ffmpeg\bin\ffprobe.exe'
        ],
        'Linux' => [
            'ffmpeg'  => '/usr/bin/ffmpeg',
            'ffprobe' => '/usr/bin/ffprobe'
        ]
    ];

    /**
     * Создает и возвращает FFMpeg инстанс
     */
    public static function getFFMpegInstance(): ?FFMpeg
    {
        $os = PHP_OS_FAMILY;

        if (!isset(self::$paths[$os])) {
            Log::error("ОС не поддерживается: $os");
            return null;
        }

        return FFMpeg::create([
            'ffmpeg.binaries'  => self::$paths[$os]['ffmpeg'],
            'ffprobe.binaries' => self::$paths[$os]['ffprobe'],
        ]);
    }

    public static function getVideoSize(string $file): array
    {
        $ffmpeg = self::getFFMpegInstance();
        if (!$ffmpeg) {
            return [];
        }

        $video = $ffmpeg->open($file);
        $dimensions = $video->getStreams()->videos()->first()->getDimensions();

        return [$dimensions->getWidth(), $dimensions->getHeight()];
    }
}
