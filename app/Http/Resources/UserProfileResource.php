<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'is_my_profile' => $this->resource['is_my_profile'],
            'user' => $this->resource['user'],
            'relationship' => $this->resource['relationship'],
            'additional_data' => $this->resource['additional_data'],
        ];
    }
}
