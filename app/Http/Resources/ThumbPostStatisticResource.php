<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThumbPostStatisticResource extends JsonResource
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
            'comments' => $this->comments_count,
            'downloads' => $this->downloads_count
        ];
    }
}
