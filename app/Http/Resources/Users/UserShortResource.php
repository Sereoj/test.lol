<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AvatarResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username,
            'slug' => $this->slug,
            'verification' => $this->verification,
            'avatar' => new AvatarResource($this->currentAvatar),
        ];
    }
}
