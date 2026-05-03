<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\Media\StorageService;

class ImageController extends Controller
{
    /**
     * Get original image file
     *
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function getOriginal(string $filename)
    {
        try {
            // Защита от path traversal атак
            if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
                $this->logWarning('Попытка path traversal атаки', [
                    'filename' => $filename,
                ]);
                return $this->errorResponse('Invalid filename', 400);
            }

            // Допустимые символы: буквы, цифры, точка, подчеркивание, дефис
            if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
                $this->logWarning('Недопустимые символы в имени файла', [
                    'filename' => $filename,
                ]);
                return $this->errorResponse('Invalid filename', 400);
            }

            $disk = StorageService::get();
            $path = 'originals/' . $filename;

            $this->logInfo('Попытка получения исходного файла', [
                'filename' => $filename,
                'disk' => $disk,
                'path' => $path,
            ]);

            if (!Storage::disk($disk)->exists($path)) {
                $this->logWarning('Исходный файл не найден', [
                    'filename' => $filename,
                    'disk' => $disk,
                    'path' => $path,
                ]);

                return $this->errorResponse('File not found', 404);
            }

            $fullPath = Storage::disk($disk)->path($path);

            $this->logInfo('Исходный файл успешно получен', [
                'filename' => $filename,
                'disk' => $disk,
            ]);

            return response()->file($fullPath);

        } catch (\Exception $e) {
            $this->logError('Не удалось получить исходный файл', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ], $e);

            return $this->errorResponse('Failed to retrieve file', 500);
        }
    }
}
