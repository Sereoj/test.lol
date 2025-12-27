<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    /**
     * Get current filesystem disk name
     */
    public static function get(): string
    {
        return config('filesystems.default', 'local');
    }

    /**
     * Get full URL path to file
     */
    public static function getPath(?string $filePath): ?string
    {
        if ($filePath === null) {
            return null;
        }

        // If already a full URL, return as is
        if (str($filePath)->startsWith('http')) {
            return $filePath;
        }

        $disk = self::get();

        // For S3 and S3-compatible storage (like Beget Cloud Storage)
        if ($disk === 's3') {
            return Storage::disk('s3')->url($filePath);
        }

        // For local and other disks
        $baseUrl = config("filesystems.disks.{$disk}.url");

        switch ($disk) {
            case 'ftp':
                return $baseUrl . 'storage/' . $filePath;
            case 'local':
            case 'public':
                return $baseUrl . '/' . $filePath;
            default:
                return $baseUrl . '/' . $filePath;
        }
    }
}
