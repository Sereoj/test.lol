<?php

namespace App\Services\Posts\Assistants;
class MediaTypeFilterService
{
    protected array $mediaTypesMap = [
        'images' => ['image/jpeg', 'image/png', 'image/webp'],
        'videos' => ['video/mp4', 'video/webm', 'video/quicktime'],
        'gifs' => ['image/gif'],
    ];

    public function apply($query, ?array $mediaTypes, string $filterMode = 'or')
    {
        // Проверяем, что $mediaTypes является массивом
        if (!is_array($mediaTypes)) {
            $mediaTypes = [];
        }

        if (empty($mediaTypes) || in_array('all', $mediaTypes, true)) {
            return; // Без фильтрации
        }

        if ($filterMode === 'or') {
            $this->applyOrFilter($query, $mediaTypes);
        } elseif ($filterMode === 'and') {
            $this->applyAndFilter($query, $mediaTypes);
        }
    }

    private function applyOrFilter($query, array $mediaTypes)
    {
        $allowedMimeTypes = [];
        foreach ($mediaTypes as $type) {
            if (isset($this->mediaTypesMap[$type])) {
                $allowedMimeTypes = array_merge($allowedMimeTypes, $this->mediaTypesMap[$type]);
            }
        }

        if (!empty($allowedMimeTypes)) {
            $query->whereHas('media', function ($query) use ($allowedMimeTypes) {
                $query->whereIn('mime_type', $allowedMimeTypes);
            });
        }
    }

    private function applyAndFilter($query, array $mediaTypes)
    {
        foreach ($mediaTypes as $type) {
            if (isset($this->mediaTypesMap[$type])) {
                $query->whereHas('media', function ($query) use ($type) {
                    $query->whereIn('mime_type', $this->mediaTypesMap[$type]);
                });
            }
        }
    }
}
