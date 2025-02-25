<?php

namespace App\Helpers;

use App\Services\Media\VideoService;

if(!function_exists('getvideosize')) {
    function getvideosize($file): array
    {
        return VideoService::getVideoSize($file);
    }
}
