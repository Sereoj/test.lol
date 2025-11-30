<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="AchievementResource",
 *     type="object",
 *     title="Achievement Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description"
 *     ),
 *     @OA\Property(
 *         property="points",
 *         type="string",
 *         description="Points"
 *     )
 * )
 */
class AchievementResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'points' => $this->points,
        ];
    }
}
