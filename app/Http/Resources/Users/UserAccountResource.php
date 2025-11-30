<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AvatarResource;
use App\Http\Resources\LocationResource;
use App\Services\Media\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserAccountResource",
 *     type="object",
 *     title="UserAccount Resource",
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
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description"
 *     ),
 *     @OA\Property(
 *         property="website",
 *         type="string",
 *         description="Website"
 *     ),
 *     @OA\Property(
 *         property="gender",
 *         type="string",
 *         description="Gender"
 *     ),
 *     @OA\Property(
 *         property="language",
 *         type="string",
 *         description="Language"
 *     ),
 *     @OA\Property(
 *         property="age",
 *         type="string",
 *         description="Age"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="object",
 *         description="Location"
 *     ),
 *     @OA\Property(
 *         property="email_verified",
 *         type="string",
 *         description="Email verified"
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
class UserAccountResource extends JsonResource
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
            'verification' => $this->verification,
            'avatar' => new AvatarResource($this->currentAvatar),
            'cover' => $this->url,
            'slug' => $this->slug,
            'email' => $this->email,
            'description' => $this->description,
            'website' => $this->website,
            'gender' => $this->gender,
            'language' => $this->language,
            'age' => $this->age,
            'location' => new LocationResource($this->location),
            'email_verified' => (bool) $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
