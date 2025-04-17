<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AvatarResource;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\OnlineStatusResource;
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
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->when($request->user() && $request->user()->id === $this->id, $this->email),
            'slug' => $this->slug,
            'verification' => $this->verification,
            'avatar' => new AvatarResource($this->currentAvatar),
            'cover' => $this->cover,
            'badge' => new BadgeResource($this->bagde),
            'online' => new OnlineStatusResource($this->onlineStatus),
            'wallet' => $this->when($request->user() && $request->user()->id === $this->id, [
                'balance' => ShortUserBalance::collection($this->userBalance)
            ]),
        ];
    }
}
