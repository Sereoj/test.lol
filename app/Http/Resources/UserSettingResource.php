<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="UserSettingResource",
 *     type="object",
 *     title="UserSetting Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="is_online",
 *         type="string",
 *         description="Is online"
 *     ),
 *     @OA\Property(
 *         property="is_preferences_feed",
 *         type="string",
 *         description="Is preferences feed"
 *     ),
 *     @OA\Property(
 *         property="preferences_feed",
 *         type="string",
 *         description="Preferences feed"
 *     )
 * )
 */
class UserSettingResource extends JsonResource
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
            'is_online' => $this->is_online,
            'is_preferences_feed' => $this->is_preferences_feed,
            'preferences_feed' => $this->preferences_feed,
        ];
    }
}
