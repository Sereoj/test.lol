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
     *
     * @param string|null $filePath File path
     * @param string|null $disk Specific disk to use (optional, uses default if not specified)
     * @return string|null
     */
    public static function getPath(?string $filePath, ?string $disk = null): ?string
    {
        if ($filePath === null) {
            return null;
        }

        // If already a full URL, return as is
        if (str($filePath)->startsWith('http')) {
            return $filePath;
        }

        // Use specified disk or default
        $disk = $disk ?? self::get();

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
                // For local disk, files are served through API route
                return $baseUrl . '/api/v1/storage/' . $filePath;
            case 'public':
                return $baseUrl . '/storage/' . $filePath;
            default:
                return $baseUrl . '/' . $filePath;
        }
    }
}
