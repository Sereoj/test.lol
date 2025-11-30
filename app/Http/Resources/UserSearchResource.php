<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserSearchResource",
 *     type="object",
 *     title="UserSearch Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="username",
 *         type="string",
 *         description="Username"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug"
 *     ),
 *     @OA\Property(
 *         property="avatar",
 *         type="object",
 *         description="Avatar"
 *     ),
 *     @OA\Property(
 *         property="verification",
 *         type="string",
 *         description="Verification"
 *     ),
 *     @OA\Property(
 *         property="online",
 *         type="object",
 *         description="Online"
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="object",
 *         description="Role"
 *     ),
 *     @OA\Property(
 *         property="followers_count",
 *         type="string",
 *         description="Followers count"
 *     )
 * )
 */
class UserSearchResource extends JsonResource
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
          'username' => $this->username,
          'slug' => $this->slug,
          'avatar' => new AvatarResource($this->currentAvatar),
          'verification' => $this->verification,
          'online' => new OnlineStatusResource($this->whenLoaded('onlineStatus')),
          'role' => new RoleResource($this->whenLoaded('role')),
          'followers_count' => $this->followers->count() ?? 0,
        ];
    }
}
