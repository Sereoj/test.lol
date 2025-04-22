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
          'id' => $this->resource['stat']->id,
          'views_count' => $this->resource['stat']->views_count,
          'isUserLiked' => $this->resource['isUserLiked'],
          'likes_count' => $this->resource['stat']->likes_count,
          'reposts_count' => $this->resource['stat']->reposts_count,
          'downloads_count' => $this->resource['stat']->downloads_count,
          'purchases_count' => $this->resource['stat']->purchases_count,
          'comments_count' => $this->resource['stat']->comments_count
        ];
    }
}
