<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserEmploymentStatusResource",
 *     type="object",
 *     title="UserEmploymentStatus Resource",
 *     @OA\Property(
 *         property="active",
 *         type="object",
 *         description="Active"
 *     ),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Items"
,
 *         @OA\Items(type="object")
 *     )
 * )
 */
class UserEmploymentStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'active' => new EmploymentStatusResource($this->resource['active']),
            'items' => EmploymentStatusResource::collection($this->resource['items'])
        ];
    }
}
