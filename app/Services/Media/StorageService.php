<?php

namespace App\Services\Media;

class StorageService
{
    public static $server = 'local';
    public static function get()
    {
        return self::$server;
    }
}
