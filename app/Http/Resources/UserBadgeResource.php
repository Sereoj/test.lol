<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserBadgeResource",
 *     type="object",
 *     title="UserBadge Resource",
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
 *     ),
 *     @OA\Property(
 *         property="is_active",
 *         type="string",
 *         description="Is active"
 *     )
 * )
 */
class UserBadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получаем все бейджи
        $badges = $this->resource->map(function ($item) {
            return [
                'id' => $item->badge->id,
                'name' => $item->badge->name,
                'color' => $item->badge->color,
                'description' => $item->badge->description,
                'options' => $item->badge->options,
                'image' => $item->badge->image,
                'is_active' => $item->is_active,
            ];
        });

        return [
            'badges' => $badges,
        ];
    }
}
