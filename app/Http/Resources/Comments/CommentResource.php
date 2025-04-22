<?php

namespace App\Http\Resources\Comments;

use App\Http\Resources\Users\UserShortResource;
use App\Http\Resources\Users\UserShortWithBalanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserShortResource($this->user),
            'content' => $this->content,
            'likes' => $this->likes, // Ресурс для лайков
            'reports' => $this->reports,
            'reposts' => $this->reposts()->count(), // Количество репостов
            'replies' => CommentResource::collection($this->replies), // Вложенные комментарии
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
