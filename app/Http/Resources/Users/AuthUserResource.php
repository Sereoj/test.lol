<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AvatarResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="AuthUserResource",
 *     type="object",
 *     title="AuthUser Resource",
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
 *         property="verification",
 *         type="string",
 *         description="Verification"
 *     ),
 *     @OA\Property(
 *         property="avatar",
 *         type="object",
 *         description="Avatar"
 *     ),
 *     @OA\Property(
 *         property="cover",
 *         type="string",
 *         description="Cover"
 *     )
 * )
 */
class AuthUserResource extends JsonResource
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
            'verification' => $this->verification,
            'avatar' => new AvatarResource($this->currentAvatar),
            'cover' => $this->url,
        ];
    }
}
