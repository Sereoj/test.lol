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
     * @OA\Get(
     *     path="/api/v1/posts/{post_id}/comments",
     *     tags={"Comments"},
     *     summary="Get all comments",
     *     description="Get all comments",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         description="Post id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Comment")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('per_page', 10);
        $sortBy = $request->input('sortBy', 'created_at');
        $order = $request->input('order', 'desc');

        $comments = $this->commentService->fetchCommentsForPost($request->post_id, $page, $limit, $sortBy, $order);

        $total = $comments->total();
        $lastPage = ceil($total / $limit);

        return $this->successResponse(CommentResource::collection($comments), [
            'total' => $total,
            'per_page' => $limit,
            'current_page' => $comments->currentPage(),
            'last_page' => $lastPage,
        ]);
    }

    // Получение конкретного комментария   
    /**
     * @OA\Get(
     *     path="/api/v1/posts/{post_id}/comments/{comment_id}",
     *     tags={"Comments"},
     *     summary="Get comment by ID",
     *     description="Get comment by ID",
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
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function show($post_id, $comment_id)
    {
        try {
            $comment = $this->commentService->fetchCommentById($comment_id);
            return $this->successResponse(new CommentResource($comment));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch comment', 500);
        }
    }

    // Обновление комментария   
    /**
     * @OA\Patch(
     *     path="/api/v1/posts/{post_id}/comments/{comment_id}",
     *     tags={"Comments"},
     *     summary="Update comment",
     *     description="Update comment",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CommentRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function update(CommentRequest $request, $post_id, $comment_id)
    {
        try {
            $this->commentService->updateComment($comment_id, $request->validated());
            return $this->successResponse(['message' => 'Comment updated successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update comment', 500);
        }
    }    /**
     * @OA\Post(
     *     path="/api/v1/posts/{post_id}/comments",
     *     tags={"Comments"},
     *     summary="Create new comment",
     *     description="Create new comment",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         description="Post id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CommentRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */


    public function store(CommentRequest $request, $slug)
    {
        try {
            $comment = new CommentResource($this->commentService->createComment($slug, $request->validated()));
            return $this->successResponse($comment,[], 201);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse('Failed to create comment', 500);
        }
    }

    // Удаление комментария   
    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{post_id}/comments/{comment_id}",
     *     tags={"Comments"},
     *     summary="Delete comment",
     *     description="Delete comment",
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
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function destroy($post_id, int $comment_id)
    {
        try {
            \Log::info('destroy', ['id' => $comment_id]);
            $this->commentService->deleteComment($comment_id);
            return $this->successResponse(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // Добавление реакции на комментарий   
    /**
     * @OA\Post(
     *     path="/api/v1/posts/{post_id}/comments/{comment_id}/react",
     *     tags={"Comments"},
     *     summary="React comment",
     *     description="React comment",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CommentReactRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function react(CommentReactRequest $request,$post_id, $comment_id)
    {
        try {
            $comment = $this->commentService->reactToComment($comment_id, $request->input('type'));
            return $this->successResponse(new UserCommentReactionResource($comment));
        } catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage(), $exception->getCode() != 0 ? $exception->getCode() : 400);
        }
/*        return $this->successResponse(
            new UserCommentReactionResource($comment)
        );*/
    }

    // Отправка жалобы на комментарий   
    /**
     * @OA\Post(
     *     path="/api/v1/posts/{post_id}/comments/{comment_id}/report",
     *     tags={"Comments"},
     *     summary="Report comment",
     *     description="Report comment",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ReportCommentRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function report(ReportCommentRequest $request,$post_id, $comment_id)
    {
        return $this->successResponse(
            $this->commentService->reportComment($comment_id, $request->input('reason'))
        );
    }

    // Репост комментария   
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Comment")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function repost($post_id, $comment_id)
    {
        return $this->successResponse(
            $this->commentService->repostComment($comment_id)
        );
    }
}
