<?php

namespace App\Http\Resources\User;

use App\Http\Resources\AvatarResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPrivateProfileResource extends BaseProfileResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge($this->Fields(), [

        ]);
    }
}
