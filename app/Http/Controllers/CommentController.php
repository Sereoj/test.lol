<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentReactRequest;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Requests\Comment\ReportCommentRequest;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\Request;

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

        return response()->json($comments);
    }

    public function store(CommentRequest $request, $post_id)
    {
        $post = Post::query()->findOrFail($post_id);

        if (! $post) {
            return response()->json(['message' => 'Post not found!'], 404);
        }

        $comment = $this->commentService->createComment($post->id, $request->validated());

        try {
            return response()->json($comment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create comment'], 500);
        }
    }

    public function show($id)
    {
        try {
            $comment = $this->commentService->getCommentsForPost($id);

            return response()->json($comment);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch comment'], 500);
        }
    }

    public function react(CommentReactRequest $request, $commentId)
    {
        return response()->json($this->commentService->reactToComment($commentId, $request->input('type')));
    }

    public function report(ReportCommentRequest $request, $commentId)
    {
        return response()->json($this->commentService->reportComment($commentId, $request->input('reason')));
    }

    public function repost($commentId)
    {
        return response()->json($this->commentService->repostComment($commentId));
    }

    public function update(CommentRequest $request, $id)
    {
        try {
            $this->commentService->updateComment($id, $request->validated());

            return response()->json(['message' => 'Comment updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update comment'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->commentService->deleteComment($id);

            return response()->json(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete comment'], 500);
        }
    }
}
