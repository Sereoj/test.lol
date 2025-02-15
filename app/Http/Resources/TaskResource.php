<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'target' => $this->target,
            'period' => $this->period,
            'type' => $this->type,
            'experience_reward' => $this->experience_reward,
            'virtual_balance_reward' => $this->virtual_balance_reward,
        ];
    }
}
