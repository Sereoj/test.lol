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
        $isDeleted = $this->deleted_at !== null;
        return [
            'id' => $this->id,
            'user' => $isDeleted ? null : new UserShortResource($this->user),
            'content' => $isDeleted ? 'Комментарий удалён' : $this->content,
            'likes' => $isDeleted ? [] : $this->likes,
            'reports' => $isDeleted ? [] : $this->reports,
            'reposts' => $isDeleted ? 0 : $this->reposts()->count(),
            'replies' => CommentResource::collection($this->replies),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'deleted' => $isDeleted,
        ];
    }
}
