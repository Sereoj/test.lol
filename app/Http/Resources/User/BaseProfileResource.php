<?php

namespace App\Http\Resources\User;

use App\Http\Resources\AvatarResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseProfileResource extends JsonResource
{
    protected function Fields(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'slug' => $this->slug,
            'avatars' => new AvatarResource($this->whenLoaded('avatars')),
        ];
    }
}
