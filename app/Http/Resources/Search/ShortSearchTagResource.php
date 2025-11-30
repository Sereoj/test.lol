<?php

namespace App\Http\Resources\Search;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="ShortSearchTagResource",
 *     type="object",
 *     title="ShortSearchTag Resource",
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
 *         property="slug",
 *         type="string",
 *         description="Slug"
 *     ),
 *     @OA\Property(
 *         property="count",
 *         type="string",
 *         description="Count"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         description="Created at"
 *     )
 * )
 */
class ShortSearchTagResource extends JsonResource
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
          'slug' => $this->slug,
          'count' => $this->count ?? 1,
          'created_at' => $this->created_at,
        ];
    }
}
