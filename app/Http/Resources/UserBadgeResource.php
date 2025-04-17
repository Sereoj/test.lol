<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получаем все бейджи
        $badges = $this->resource->map(function ($item) {
            return [
                'id' => $item->badge->id,
                'name' => $item->badge->name,
                'color' => $item->badge->color,
                'description' => $item->badge->description,
                'options' => $item->badge->options,
                'image' => $item->badge->image,
                'is_active' => $item->is_active,
            ];
        });

        return [
            'badges' => $badges,
        ];
    }
}
