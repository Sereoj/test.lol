<?php

namespace App\Http\Resources\Posts;

use App\Http\Resources\Apps\AppShortResource;
use App\Http\Resources\Media\MediaShortResource;
use App\Http\Resources\ShortCategoryResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Users\UserShortResource;
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
            'slug' => $this->slug,
            'content' => $this->content,
            'settings' => [
                'status' => $this->status,
                'is_adult_content' => $this->is_adult_content,
                'is_nsfl_content' => $this->is_nsfl_content,
                'has_copyright' => $this->has_copyright,
                'is_free' => $this->is_free,
                'advanced' => $this->settings
            ],
            'media' => MediaShortResource::collection($this->media),
            'user' => new UserShortResource($this->user),
            'statistics' => new PostStatisticResource($this->statistics),
            'category' => new ShortCategoryResource($this->category),
            'apps' => AppShortResource::collection($this->apps),
            'tags' => TagShortResource::collection($this->tags),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
