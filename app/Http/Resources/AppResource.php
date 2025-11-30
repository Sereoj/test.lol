<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="AppResource",
 *     type="object",
 *     title="App Resource",
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
 *         property="path",
 *         type="string",
 *         description="Path"
 *     )
 * )
 */
class AppResource extends JsonResource
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
            'path' => $this->path,
        ];
    }
}
