<?php

namespace App\Http\Resources\Media;

use App\Http\Resources\Users\UserShortWithBalanceResource;
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
            return $media->reject(fn ($item) => $item->type != Media::STATUS_ORIGINAL);
        });

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'is_adult_content' => $this->is_adult_content,
            'is_nsfl_content' => $this->is_nsfl_content,
            'is_free' => $this->is_free,
            'has_copyright' => $this->has_copyright,
            'user' => UserShortWithBalanceResource::make($this->whenLoaded('user')),
            'media' => MediaShortResource::groupMedia($filteredMedia),
            'likes_count' => $this->likes_count,
            'views_count' => $this->views_count,
            'comments_count' => $this->comments_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
