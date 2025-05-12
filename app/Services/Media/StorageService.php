<?php

namespace App\Services\Media;

class StorageService
{
    public static $server = 'ftp';
    public static function get()
    {
        return self::$server;
    }

    public static function getPath(string $filePath)
    {
        $path = config('filesystems.disks.' . self::$server.'.url');
        switch (self::$server) {
            case 'ftp':
                return $path .'storage/'. $filePath;
            case 'local':
                return $path .'/'. $filePath;
            default:
                return '';
        }
    }
}
