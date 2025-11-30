<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="StorePostResource",
 *     type="object",
 *     title="StorePost Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Content"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Slug"
 *     )
 * )
 */
class StorePostResource extends JsonResource
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
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'slug' => $this->slug
        ];
    }
}
