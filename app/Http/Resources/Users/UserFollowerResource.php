<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserFollowerResource",
 *     type="object",
 *     title="UserFollower Resource",
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/UserShortResource",
 *         description="User data"
 *     ),
 *     @OA\Property(
 *         property="is_following",
 *         type="boolean",
 *         description="Is current user following this user"
 *     )
 * )
 */
class UserFollowerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserShortResource($this->resource),
            'is_following' => $this->is_following ?? false,
        ];
    }
}
