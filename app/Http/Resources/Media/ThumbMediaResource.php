<?php

namespace App\Http\Resources\Media;

use App\Http\Resources\Users\UserShortWithBalanceResource;
use App\Models\Media\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ThumbMediaResource",
 *     type="object",
 *     title="Thumb Media Resource",
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
 *         property="is_adult_content",
 *         type="boolean",
 *         description="Is adult content"
 *     ),
 *     @OA\Property(
 *         property="is_nsfl_content",
 *         type="boolean",
 *         description="Is nsfl content"
 *     ),
 *     @OA\Property(
 *         property="is_free",
 *         type="boolean",
 *         description="Is free"
 *     ),
 *     @OA\Property(
 *         property="has_copyright",
 *         type="boolean",
 *         description="Has copyright"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="User"
 *     ),
 *     @OA\Property(
 *         property="media",
 *         type="array",
 *         description="Media",
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="likes_count",
 *         type="integer",
 *         description="Likes count"
 *     ),
 *     @OA\Property(
 *         property="views_count",
 *         type="integer",
 *         description="Views count"
 *     ),
 *     @OA\Property(
 *         property="comments_count",
 *         type="integer",
 *         description="Comments count"
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
class ThumbMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $filteredMedia = $this->whenLoaded('media', function ($media) {
            return $media->reject(fn ($item) => $item->type != Media::STATUS_ORIGINAL);
        });

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'is_adult_content' => $this->is_adult_content,
            'is_nsfl_content' => $this->is_nsfl_content,
            'is_free' => $this->is_free,
            'has_copyright' => $this->has_copyright,
            'user' => UserShortWithBalanceResource::make($this->whenLoaded('user')),
            'media' => MediaShortResource::groupMedia($filteredMedia),
            'likes_count' => $this->likes_count,
            'views_count' => $this->views_count,
            'comments_count' => $this->comments_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
