<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="PostStatResource",
 *     type="object",
 *     title="PostStat Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="views",
 *         type="string",
 *         description="Views"
 *     ),
 *     @OA\Property(
 *         property="isUserLiked",
 *         type="string",
 *         description="IsUserLiked"
 *     ),
 *     @OA\Property(
 *         property="likes",
 *         type="string",
 *         description="Likes"
 *     ),
 *     @OA\Property(
 *         property="reposts",
 *         type="string",
 *         description="Reposts"
 *     ),
 *     @OA\Property(
 *         property="downloads",
 *         type="string",
 *         description="Downloads"
 *     ),
 *     @OA\Property(
 *         property="purchases",
 *         type="string",
 *         description="Purchases"
 *     ),
 *     @OA\Property(
 *         property="comments",
 *         type="string",
 *         description="Comments"
 *     )
 * )
 */
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
          'views' => $this->resource['stat']->views_count,
          'isUserLiked' => $this->resource['isUserLiked'],
          'likes' => $this->resource['stat']->likes_count,
          'reposts' => $this->resource['stat']->reposts_count,
          'downloads' => $this->resource['stat']->downloads_count,
          'purchases' => $this->resource['stat']->purchases_count,
          'comments' => $this->resource['stat']->comments_count
        ];
    }
}
