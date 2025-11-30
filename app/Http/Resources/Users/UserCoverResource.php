<?php

namespace App\Http\Resources\Users;

use App\Services\Media\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserCoverResource",
 *     type="object",
 *     title="UserCover Resource",
 *     @OA\Property(
 *         property="cover",
 *         type="string",
 *         description="Cover"
 *     )
 * )
 */
class UserCoverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'cover' => $this->url,
        ];
    }
}
