<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\AvatarResource;
use App\Http\Resources\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAccountResource extends JsonResource
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
            'verification' => $this->verification,
            'avatar' => new AvatarResource($this->currentAvatar),
            'cover' => 'http://test/public/'.$this->cover,
            'slug' => $this->slug,
            'email' => $this->email,
            'description' => $this->description,
            'website' => $this->website,
            'gender' => $this->gender,
            'language' => $this->language,
            'age' => $this->age,
            'location' => new LocationResource($this->location),
            'email_verified' => (bool) $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
