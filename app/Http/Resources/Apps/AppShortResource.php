<?php

namespace App\Http\Resources\Apps;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="AppShortResource",
 *     type="object",
 *     title="AppShort Resource",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name"
 *     ),
 *     @OA\Property(
 *         property="src",
 *         type="string",
 *         description="Src"
 *     )
 * )
 */
class AppShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'src' => $this->path,
        ];
    }
}
