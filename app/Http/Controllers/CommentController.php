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

// Контроллер для работы с комментариями
class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    // Получение списка комментариев для поста
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
    public function update(CommentRequest $request, $post_id, $comment_id)
    {
        try {
            $this->commentService->updateComment($comment_id, $request->validated());
            return $this->successResponse(['message' => 'Comment updated successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update comment', 500);
        }
    }

    public function store(CommentRequest $request, $slug)
    {
        try {
            $comment = new CommentResource($this->commentService->createComment($slug, $request->validated()));
            return $this->successResponse($comment, [], 201);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse('Failed to create comment', 500);
        }
    }

    // Удаление комментария
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
    public function react(CommentReactRequest $request, $post_id, $comment_id)
    {
        try {
            $comment = $this->commentService->reactToComment($comment_id, $request->input('type'));
            return $this->successResponse(new UserCommentReactionResource($comment));
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), $exception->getCode() != 0 ? $exception->getCode() : 400);
        }
        /*        return $this->successResponse(
                    new UserCommentReactionResource($comment)
                );*/
    }

    // Отправка жалобы на комментарий
    public function report(ReportCommentRequest $request, $post_id, $comment_id)
    {
        return $this->successResponse(
            $this->commentService->reportComment($comment_id, $request->input('reason'))
        );
    }

    // Репост комментария
    public function repost($post_id, $comment_id)
    {
        return $this->successResponse(
            $this->commentService->repostComment($comment_id)
        );
    }
}
