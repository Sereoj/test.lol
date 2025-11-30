<?php

namespace App\Http\Resources\Posts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="PostStatisticResource",
 *     type="object",
 *     title="PostStatistic Resource",
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
 *     ),
 *     @OA\Property(
 *         property="score",
 *         type="string",
 *         description="Score"
 *     )
 * )
 */
class PostStatisticResource extends JsonResource
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
            'reposts' => $this->reposts_count,
            'downloads' => $this->downloads_count,
            'purchases' => $this->purchases_count,
            'comments' => $this->comments_count,
            'score' => $this->engagement_score
        ];
    }
}
