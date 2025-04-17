<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
