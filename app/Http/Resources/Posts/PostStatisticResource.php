<?php

namespace App\Http\Resources\Posts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostStatisticResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'views' => $this->views_count,
            'likes' => $this->likes_count,
            'reposts' => $this->reposts_count,
            'downloads' => $this->downloads_count,
            'purchases' => $this->purchases_count,
            'comments' => $this->comments_count,
            'score' => $this->engagement_score
        ];
    }
}
