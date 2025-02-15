<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
