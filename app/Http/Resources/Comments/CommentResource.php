<?php

namespace App\Http\Resources\Comments;

use App\Http\Resources\UserShortResource;
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
            'likes' => CommentLikeResource::collection($this->whenLoaded('likes')), // Ресурс для лайков
            'reports' => CommentReportResource::collection($this->whenLoaded('reports')),
            'reposts' => $this->reposts()->count(), // Количество репостов
            'replies' => CommentResource::collection($this->whenLoaded('replies')), // Вложенные комментарии
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
