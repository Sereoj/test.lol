<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="OnlineStatusResource",
 *     type="object",
 *     title="OnlineStatus Resource",
 *     @OA\Property(
 *         property="last_activity",
 *         type="string",
 *         description="Last activity"
 *     ),
 *     @OA\Property(
 *         property="device_type",
 *         type="string",
 *         description="Device type"
 *     ),
 *     @OA\Property(
 *         property="ip_address",
 *         type="string",
 *         description="Ip address"
 *     ),
 *     @OA\Property(
 *         property="is_online",
 *         type="string",
 *         description="Is online"
 *     )
 * )
 */
class OnlineStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'last_activity' => $this->last_activity,
/*            'device_type' => $this->device_type,
            'ip_address' => $this->ip_address,*/
            'is_online' => $this->isOnline(),
        ];
    }
}
