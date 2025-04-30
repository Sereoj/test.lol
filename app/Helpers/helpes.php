<?php

namespace App\Helpers;

use App\Services\Media\VideoService;

if (!function_exists('getvideosize')) {
    function getvideosize($file): array
    {
        return VideoService::getVideoSize($file);
    }
}

if (!function_exists('sanitizeText')) {
    function sanitizeText($text) {

        $text = preg_replace('/https?:\/\/[^\s]+/i', '', $text);
        $text = preg_replace('/www\.[^\s]+/i', '', $text);
        $text = strip_tags($text);
        $text = preg_replace('/<\?(php)?/i', '', $text);
        $text = preg_replace('/(on\w+\s*=\s*["\'][^"\']*["\'])/i', '', $text); // JS-обработчики
        $text = preg_replace('/(script|alert|eval|expression|base64_decode|UNION|SELECT|INSERT|DELETE|DROP|UPDATE)/i', '', $text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
