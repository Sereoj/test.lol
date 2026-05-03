<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GravatarService
{
    protected function Get(string $username, string $backgroundColor)
    {
        try {
            $response = Http::get($this->getPath($username, $backgroundColor));

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (Exception $e) {
            Log::error('Ошибка получения аватара из Gravatar: ' . $e->getMessage());
            return null;
        }
    }

    public function getPath(string $username, string $backgroundColor = '#caeaff')
    {
        return 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($username) . '&backgroundColor=' . $backgroundColor;
    }
    public function GetAvatar(string $username, string $backgroundColor)
    {
        return $this->Get($username, $backgroundColor);
    }
}
