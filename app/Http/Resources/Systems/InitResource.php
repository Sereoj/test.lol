<?php

namespace App\Http\Resources\Systems;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'tags' => [],
          'hits' => [],
          'backgrounds' => [],
          'language' => app()->getLocale(),
          'version' => getenv('APP_VERSION'),
        ];
    }
}
