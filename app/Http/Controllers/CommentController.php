<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentReactRequest;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Requests\Comment\ReportCommentRequest;
use App\Http\Resources\comment\UserCommentReactionResource;
use App\Http\Resources\Comments\CommentCollection;
use App\Http\Resources\Comments\CommentResource;
use App\Models\Posts\Post;
use App\Services\Comments\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function index(Request $request)
    {
        $comments = $this->commentService->getCommentsForPost($request->post_id);
        return $this->successResponse($comments);
    }

    public function show($id)
    {
        try {
            $comment = $this->commentService->getCommentsForPost($id);
            return $this->successResponse($comment);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch comment', 500);
        }
    }

    public function update(CommentRequest $request, $id)
    {
        try {
            $postId = $this->commentService->updateComment($id, $request->validated())->first()->id;
            return $this->successResponse(['message' => 'Comment updated successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update comment', 500);
        }
    }

    public function store(CommentRequest $request, $slug)
    {
        try {
            $comment = new CommentResource($this->commentService->createComment($slug, $request->validated()));
            return $this->successResponse($comment, 201);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse('Failed to create comment', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $postId = $this->commentService->deleteComment($id)->first()->id;
            return $this->successResponse(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete comment', 500);
        }
    }

    public function react(CommentReactRequest $request, $commentId)
    {
        $comment = $this->commentService->reactToComment($commentId, $request->input('type'));
        return $this->successResponse(
            new UserCommentReactionResource($comment)
        );
    }

    public function report(ReportCommentRequest $request, $commentId)
    {
        return $this->successResponse(
            $this->commentService->reportComment($commentId, $request->input('reason'))
        );
    }

    public function repost($commentId)
    {
        return $this->successResponse(
            $this->commentService->repostComment($commentId)
        );
    }
}
