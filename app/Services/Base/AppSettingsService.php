<?php

namespace App\Services\Base;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AppSettingsService
{
    private static string $path = '';
    private static string $fileName = 'settings.json';
    private array $settings = [];

    public function __construct()
    {
        self::$path = storage_path('app/settings');
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $filePath = self::$path.DIRECTORY_SEPARATOR.self::$fileName;

        if (! File::exists($filePath)) {
            $this->save();
        }

        try {
            $content = File::get($filePath);
            $this->settings = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException("Failed to decode settings file: {$e->getMessage()}");
        }
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->settings;
        }

        return Arr::get($this->settings, $key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        Arr::set($this->settings, $key, $value);
    }

    public function save(): void
    {
        $filePath = self::$path.DIRECTORY_SEPARATOR.self::$fileName;

        try {
            File::put($filePath, json_encode($this->settings, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            Log::error('Failed to save settings file: ',
            [
                'file' => $filePath,
                'line' => $e->getLine(),
                'message' => $e->getMessage()
            ]);
            throw new \RuntimeException("Failed to save settings file: {$e->getMessage()}");
        }
    }
}
