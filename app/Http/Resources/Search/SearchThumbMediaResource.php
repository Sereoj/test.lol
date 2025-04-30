<?php

namespace App\Http\Resources\Search;

use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Users\UserShortResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchThumbMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'is_adult_content' => $this->is_adult_content,
            'is_nsfl_content' => $this->is_nsfl_content,
            'has_copyright' => $this->has_copyright,
            'category' => $this->whenLoaded('category'),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'user' => new UserShortResource($this->whenLoaded('user')),
        ];
    }
}
