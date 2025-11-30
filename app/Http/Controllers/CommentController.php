<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentReactRequest;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Requests\Comment\ReportCommentRequest;
use App\Http\Resources\Comments\UserCommentReactionResource;
use App\Http\Resources\Comments\CommentCollection;
use App\Http\Resources\Comments\CommentResource;
use App\Services\Comments\CommentService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

// Контроллер для работы с комментариями
class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    // Получение списка комментариев для поста   
    
    /**
     * @OA\Post(
     *     path="/api/v1/posts/{post_id}/comments/{comment_id}/repost",
     *     tags={"Comments"},
     *     summary="Repost comment",
     *     description="Repost comment",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         description="Post id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="comment_id",
     *         in="path",
     *         required=true,
     *         description="Comment id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
public function repost($post_id, $comment_id)
    {
        return $this->successResponse(
            $this->commentService->repostComment($comment_id)
        );
    }
}
