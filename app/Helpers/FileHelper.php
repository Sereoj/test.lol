<?php

namespace App\Helpers;

class FileHelper
{
    //image/gif вернет gif
    //image/jpeg, image/png, image/webp вернут image
    //video/mp4, video/avi вернут video
    public static function determineFileType(string $mimeType): string
    {
        if (str_contains($mimeType, 'gif')) {
            return 'gif';
        }

        if (str_contains($mimeType, 'video')) {
            return 'video';
        }

        if (str_contains($mimeType, 'image')) {
            return 'image';
        }

        return 'unknown';
    }
}
