<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserEmploymentStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'active' => new EmploymentStatusResource($this->resource['active_employment_status']),
            'items' => EmploymentStatusResource::collection($this->resource['employment_statuses'])
        ];
    }
}
