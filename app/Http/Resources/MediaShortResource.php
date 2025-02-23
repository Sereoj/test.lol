<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->getMediaType(),
            'src' => asset('storage/' . $this->file_path),
        ];
    }

    private function getMediaType(): string
    {
        if (str_starts_with($this->mime_type, 'video')) {
            return 'video';
        }

        if (str_starts_with($this->mime_type, 'image/gif')) {
            return 'gif';
        }

        return 'image';
    }

    public static function groupMedia($media): array
    {
        if ($media->isEmpty()) {
            return [];
        }

        $images = $media->filter(fn($item) => str_starts_with($item->mime_type, 'image'))->values();
        $videos = $media->filter(fn($item) => str_starts_with($item->mime_type, 'video'))->values();

        $result = [];

        // Если меньше 4 файлов одного типа → сразу в `group`
        if ($images->count() > 0 && $images->count() <= 4) {
            $result[] = [
                'type' => 'group',
                'group' => $images->map(fn($item) => new self($item))->toArray()
            ];
        }

        if ($videos->count() > 0 && $videos->count() <= 4) {
            $result[] = [
                'type' => 'group',
                'group' => $videos->map(fn($item) => new self($item))->toArray()
            ];
        }

        // Если файлов больше 4, но меньше 10 – делим на группы по 4
        if ($images->count() > 4) {
            $result = array_merge($result, self::splitIntoGroups($images));
        }

        if ($videos->count() > 4) {
            $result = array_merge($result, self::splitIntoGroups($videos));
        }

        $allMedia = collect($result)->flatten(1);
        if ($allMedia->count() > 10) {
            $visibleMedia = $allMedia->take(9)->toArray();
            $hiddenCount = $allMedia->count() - 9;

            $visibleMedia[] = [
                'type' => 'more',
                'count' => $hiddenCount
            ];

            return $visibleMedia;
        }

        return $result;
    }

    private static function splitIntoGroups($media): array
    {
        return $media->chunk(4)->map(function ($chunk) {
            return [
                'type' => 'group',
                'group' => $chunk->map(fn($item) => new self($item))->toArray()
            ];
        })->toArray();
    }
}
