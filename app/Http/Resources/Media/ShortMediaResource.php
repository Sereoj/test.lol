<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ShortMediaResource",
 *     type="object",
 *     title="ShortMedia Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="file_path",
 *         type="string",
 *         description="File path"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type"
 *     ),
 *     @OA\Property(
 *         property="mime_type",
 *         type="string",
 *         description="Mime type"
 *     ),
 *     @OA\Property(
 *         property="size",
 *         type="string",
 *         description="Size"
 *     )
 * )
 */
class ShortMediaResource extends JsonResource
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
            'file_path' => $this->url,
            'type' => $this->type,
            'mime_type' => $this->mime_type,
            'size' => $this->size
        ];
    }
}
