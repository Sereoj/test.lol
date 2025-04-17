<?php

namespace App\Http\Resources\comment;

use App\Http\Resources\Users\UserShortResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCommentReactionResource extends JsonResource
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
            'type' => $this->type,
            'user' => new UserShortResource($this->user_id)
        ];
    }
}
