<?php

namespace App\Http\Resources\Posts;

use App\Http\Resources\Apps\AppShortResource;
use App\Http\Resources\Media\MediaShortResource;
use App\Http\Resources\ShortCategoryResource;
use App\Http\Resources\Tag\TagShortResource;
use App\Http\Resources\Users\UserShortWithBalanceResource;
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
            'id' => $this->resource['post']->id,
            'title' => $this->resource['post']->title,
            'slug' => $this->resource['post']->slug,
            'content' => $this->resource['post']->content,
            'settings' => [
                'status' => $this->resource['post']->status,
                'is_adult_content' => $this->resource['post']->is_adult_content,
                'is_nsfl_content' => $this->resource['post']->is_nsfl_content,
                'has_copyright' => $this->resource['post']->has_copyright,
                'is_free' => $this->resource['post']->is_free,
                'advanced' => $this->resource['post']->settings
            ],
            'isUserLiked' => $this->resource['isUserLiked'],
            'isFavorited' => $this->resource['isFavorited'],
            'media' => MediaShortResource::collection($this->resource['post']->media),
            'user' => new UserShortWithBalanceResource($this->resource['post']->user),
            'statistics' => new PostStatisticResource($this->resource['post']->statistics),
            'category' => new ShortCategoryResource($this->resource['post']->category),
            'apps' => AppShortResource::collection($this->resource['post']->apps),
            'tags' => TagShortResource::collection($this->resource['post']->tags),
            'created_at' => $this->resource['post']->created_at,
            'updated_at' => $this->resource['post']->updated_at,
        ];
    }
}
