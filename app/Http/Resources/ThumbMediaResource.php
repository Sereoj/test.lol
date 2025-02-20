<?php

namespace App\Http\Resources;

use App\Models\Media\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThumbMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $filteredMedia = $this->whenLoaded('media', function ($media) {
            return $media->reject(fn($item) => $item->type === Media::STATUS_ORIGINAL);
        });

        $imageCount = $filteredMedia->filter(fn($item) => str_starts_with($item->mime_type, 'image'))->count();
        $hasVideo = $filteredMedia->contains(fn($item) => str_starts_with($item->mime_type, 'video'));

        $mediaType = match (true) {
            $hasVideo => 'video',
            $imageCount === 1 => 'single',
            $imageCount === 2 => 'double',
            $imageCount === 3 => 'triple',
            $imageCount >= 4 => 'quad',
            default => null
        };

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'is_adult_content' => $this->is_adult_content,
            'is_nsfl_content' => $this->is_nsfl_content,
            'is_free' => $this->is_free,
            'has_copyright' => $this->has_copyright,
            'user' => UserShortResource::make($this->whenLoaded('user')),
            'type' => $mediaType,
            'media' => MediaShortResource::collection($this->whenLoaded('media',  function ($media) {
                return $media->reject(fn($item) => $item->type === Media::STATUS_ORIGINAL);
            }))
        ];
    }
}
