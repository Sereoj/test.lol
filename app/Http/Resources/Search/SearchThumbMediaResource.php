<?php

namespace App\Http\Resources\Search;

use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Users\UserShortResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="SearchThumbMediaResource",
 *     type="object",
 *     title="SearchThumbMedia Resource",
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
 *         property="category",
 *         type="string",
 *         description="Category"
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
 *     )
 * )
 */
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
