<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserProfileResource",
 *     type="object",
 *     title="UserProfile Resource",
 *     @OA\Property(
 *         property="is_my_profile",
 *         type="string",
 *         description="Is my profile"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="string",
 *         description="User"
 *     ),
 *     @OA\Property(
 *         property="relationship",
 *         type="string",
 *         description="Relationship"
 *     ),
 *     @OA\Property(
 *         property="additional_data",
 *         type="string",
 *         description="Additional data"
 *     )
 * )
 */
class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'is_my_profile' => $this->resource['is_my_profile'] ?? false,
            'user' => $this->resource['user'] ?? [],
            'relationship' => array_merge(
                $this->resource['relationship'] ?? []
            ),
            'additional_data' => $this->resource['additional_data'] ?? [],
        ];
    }
}
