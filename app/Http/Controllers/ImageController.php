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
            $disk = StorageService::get();
            $path = 'originals/' . $filename;

            $this->logInfo('Attempting to retrieve original file', [
                'filename' => $filename,
                'disk' => $disk,
                'path' => $path,
            ]);

            if (!Storage::disk($disk)->exists($path)) {
                $this->logWarning('Original file not found', [
                    'filename' => $filename,
                    'disk' => $disk,
                    'path' => $path,
                ]);

                return $this->errorResponse('File not found', 404);
            }

            $fullPath = Storage::disk($disk)->path($path);

            $this->logInfo('Original file retrieved successfully', [
                'filename' => $filename,
                'disk' => $disk,
            ]);

            return response()->file($fullPath);

        } catch (\Exception $e) {
            $this->logError('Failed to retrieve original file', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ], $e);

            return $this->errorResponse('Failed to retrieve file', 500);
        }
    }
}
