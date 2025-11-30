<?php

namespace App\Http\Resources\Comments;

use App\Http\Resources\Users\UserShortResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserCommentReactionResource",
 *     type="object",
 *     title="UserCommentReaction Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="User"
 *     )
 * )
 */
class UserCommentReactionResource extends JsonResource
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
            'type' => $this->type,
            'user' => new UserShortResource($this->user)
        ];
    }
}
