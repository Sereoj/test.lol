<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentReactRequest;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Requests\Comment\ReportCommentRequest;
use App\Models\Posts\Post;
use App\Services\CommentService;
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
        $cacheKey = 'comments_post_'.$request->post_id;
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $comments = $this->commentService->getCommentsForPost($request->post_id);

        Cache::put($cacheKey, $comments, now()->addMinutes(60));

        return response()->json($comments);
    }

    public function store(CommentRequest $request, $post_id)
    {
        $post = Post::query()->findOrFail($post_id);

        if (! $post) {
            return response()->json(['message' => 'Post not found!'], 404);
        }

        $comment = $this->commentService->createComment($post->id, $request->validated());

        Cache::forget('comments_post_'.$post_id);

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
            $postId = $this->commentService->updateComment($id, $request->validated())->first()->id;
            Cache::forget('comments_post_'.$postId);

            return response()->json(['message' => 'Comment updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update comment'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $postId = $this->commentService->deleteComment($id)->first()->id;
            Cache::forget('comments_post_'.$postId);

            return response()->json(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete comment'], 500);
        }
    }
}
