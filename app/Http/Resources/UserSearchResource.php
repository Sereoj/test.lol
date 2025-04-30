<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSearchResource extends JsonResource
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
          'username' => $this->username,
          'slug' => $this->slug,
          'avatar' => new AvatarResource($this->currentAvatar),
          'verification' => $this->verification,
          'online' => new OnlineStatusResource($this->whenLoaded('onlineStatus')),
          'role' => new RoleResource($this->whenLoaded('role')),
          'followers_count' => $this->followers->count() ?? 0,
        ];
    }
}
