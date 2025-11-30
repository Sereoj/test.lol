<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="MediaResource",
 *     type="object",
 *     title="Media Resource",
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
 *         property="file_path",
 *         type="string",
 *         description="File path"
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
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="string",
 *         description="User id"
 *     )
 * )
 */
class MediaResource extends JsonResource
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
            'file_path' => $this->url,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'type' => $this->type,
            'user_id' => $this->user_id,
        ];
    }
}
