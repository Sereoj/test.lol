<?php

namespace App\Http\Resources\Comments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="CommentLikeResource",
 *     type="object",
 *     title="CommentLike Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="string",
 *         description="User id"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type"
 *     )
 * )
 */
class CommentLikeResource extends JsonResource
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
            'user_id' => $this->user_id,
            'type' => $this->type, // 'like' или 'dislike'
        ];
    }
}
