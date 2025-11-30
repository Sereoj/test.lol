<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="BadgeResource",
 *     type="object",
 *     title="Badge Resource",
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
 *         property="color",
 *         type="string",
 *         description="Color"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description"
 *     ),
 *     @OA\Property(
 *         property="options",
 *         type="string",
 *         description="Options"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="Image"
 *     )
 * )
 */
class BadgeResource extends JsonResource
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
          'color' => $this->color,
          'description' => $this->description,
          //'options' => $this->options,
          'image' => $this->image,
        ];
    }
}
