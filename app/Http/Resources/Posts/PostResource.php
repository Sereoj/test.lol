<?php

namespace App\Http\Resources\Posts;

use App\Http\Resources\Apps\AppShortResource;
use App\Http\Resources\Media\MediaShortResource;
use App\Http\Resources\ShortCategoryResource;
use App\Http\Resources\Tag\TagShortResource;
use App\Http\Resources\Users\UserShortResource;
use App\Http\Resources\Users\UserShortWithBalanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="PostResource",
 *     type="object",
 *     title="Post Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Content"
 *     ),
 *     @OA\Property(
 *         property="settings",
 *         type="array",
 *         description="Settings"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status"
 *     ),
 *     @OA\Property(
 *         property="is_adult_content",
 *         type="string",
 *         description="Is adult content"
 *     ),
 *     @OA\Property(
 *         property="is_nsfl_content",
 *         type="string",
 *         description="Is nsfl content"
 *     ),
 *     @OA\Property(
 *         property="has_copyright",
 *         type="string",
 *         description="Has copyright"
 *     ),
 *     @OA\Property(
 *         property="is_free",
 *         type="string",
 *         description="Is free"
 *     ),
 *     @OA\Property(
 *         property="advanced",
 *         type="string",
 *         description="Advanced"
 *     ),
 *     @OA\Property(
 *         property="isUserLiked",
 *         type="string",
 *         description="IsUserLiked"
 *     ),
 *     @OA\Property(
 *         property="isFavorited",
 *         type="string",
 *         description="IsFavorited"
 *     ),
 *     @OA\Property(
 *         property="media",
 *         type="array",
 *         description="Media"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="User"
 *     ),
 *     @OA\Property(
 *         property="statistics",
 *         type="object",
 *         description="Statistics"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="object",
 *         description="Category"
 *     ),
 *     @OA\Property(
 *         property="apps",
 *         type="array",
 *         description="Apps"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="collaborators",
 *         type="array",
 *         description="Соавторы поста",
 *         @OA\Items(ref="#/components/schemas/UserShortResource")
 *     ),
 *     @OA\Property(
 *         property="tags",
 *         type="array",
 *         description="Tags"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         description="Created at"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         description="Updated at"
 *     )
 * )
 */
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
            'collaborators' => UserShortResource::collection($this->resource['post']->collaborators),
            'tags' => TagShortResource::collection($this->resource['post']->tags),
            'created_at' => $this->resource['post']->created_at,
            'updated_at' => $this->resource['post']->updated_at,
        ];
    }
}
