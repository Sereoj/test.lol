<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ThumbPostStatisticResource",
 *     type="object",
 *     title="ThumbPostStatistic Resource",
 *     @OA\Property(
 *         property="views",
 *         type="string",
 *         description="Views"
 *     ),
 *     @OA\Property(
 *         property="likes",
 *         type="string",
 *         description="Likes"
 *     ),
 *     @OA\Property(
 *         property="comments",
 *         type="string",
 *         description="Comments"
 *     ),
 *     @OA\Property(
 *         property="downloads",
 *         type="string",
 *         description="Downloads"
 *     )
 * )
 */
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
