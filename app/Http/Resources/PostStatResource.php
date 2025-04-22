<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostStatResource extends JsonResource
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
          'views_count' => $this->views_count,
          'likes_count' => $this->likes_count,
          'reposts_count' => $this->reposts_count,
          'downloads_count' => $this->downloads_count,
          'purchases_count' => $this->purchases_count,
          'comments_count' => $this->comments_count
        ];
    }
}
