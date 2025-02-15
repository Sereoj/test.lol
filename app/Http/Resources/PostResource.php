<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'content' => $this->content,
            'category_id' => $this->category_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'relevance_score' => $this->relevance_score,
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'apps' => AppResource::collection($this->whenLoaded('apps')),
            'user' => UserShortResource::make($this->whenLoaded('user')),
        ];
    }
}
