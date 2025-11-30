<?php

namespace App\Http\Resources\Comments;

use App\Http\Resources\Users\UserShortResource;
use App\Http\Resources\Users\UserShortWithBalanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="CommentResource",
 *     type="object",
 *     title="Comment Resource",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Id"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         description="User"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Content"
 *     ),
 *     @OA\Property(
 *         property="likes",
 *         type="string",
 *         description="Likes"
 *     ),
 *     @OA\Property(
 *         property="reports",
 *         type="string",
 *         description="Reports"
 *     ),
 *     @OA\Property(
 *         property="reposts",
 *         type="string",
 *         description="Reposts"
 *     ),
 *     @OA\Property(
 *         property="replies",
 *         type="array",
 *         description="Replies"
,
 *         @OA\Items(type="object")
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         description="Created at"
 *     ),
 *     @OA\Property(
 *         property="deleted",
 *         type="string",
 *         description="Deleted"
 *     )
 * )
 */
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
