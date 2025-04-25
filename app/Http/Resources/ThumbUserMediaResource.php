<?php

namespace App\Http\Resources;

use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Media\MediaShortResource;
use App\Http\Resources\Users\UserShortResource;
use App\Models\Media\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThumbUserMediaResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $filteredMedia = $this->whenLoaded('media', function ($media) {
            return $media->reject(fn ($item) => $item->type != Media::STATUS_ORIGINAL);
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'media' => MediaShortResource::groupMedia($filteredMedia),
            'user' => new UserShortResource($this->whenLoaded('user')),
            'statistics' => new ThumbPostStatisticResource($this->statistics),
            'price_info' => $this->price,
            'is_paid' => !$this->is_free
        ];
    }
}
