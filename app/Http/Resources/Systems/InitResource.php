<?php

namespace App\Http\Resources\Systems;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="InitResource",
 *     type="object",
 *     title="Init Resource",
 *     @OA\Property(
 *         property="tags",
 *         type="string",
 *         description="Tags"
 *     ),
 *     @OA\Property(
 *         property="hits",
 *         type="array",
 *         description="Hits"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="backgrounds",
 *         type="array",
 *         description="Backgrounds"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="language",
 *         type="string",
 *         description="Language"
 *     ),
 *     @OA\Property(
 *         property="version",
 *         type="string",
 *         description="Version"
 *     )
 * )
 */
class InitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'tags' => $this->tags,
          'hits' => [],
          'backgrounds' => [],
          'language' => app()->getLocale(),
          'version' => getenv('APP_VERSION'),
        ];
    }
}
