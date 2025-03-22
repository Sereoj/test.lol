<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CommentReactRequest;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Requests\Comment\ReportCommentRequest;
use App\Models\Posts\Post;
use App\Services\Comments\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    protected CommentService $commentService;
    
    private const CACHE_KEY_COMMENTS = 'comments_post_';
    private const CACHE_MINUTES = 60;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function index(Request $request)
    {
        $cacheKey = self::CACHE_KEY_COMMENTS . $request->post_id;
        
        $comments = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($request) {
            return $this->commentService->getCommentsForPost($request->post_id);
        });

        return $this->successResponse($comments);
    }

    public function store(CommentRequest $request, $post_id)
    {
        $post = Post::query()->findOrFail($post_id);

        if (!$post) {
            return $this->errorResponse('Post not found!', 404);
        }

        try {
            $comment = $this->commentService->createComment($post->id, $request->validated());
            $this->forgetCache(self::CACHE_KEY_COMMENTS . $post_id);
            
            return $this->successResponse($comment, 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create comment', 500);
        }
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

    public function react(CommentReactRequest $request, $commentId)
    {
        return $this->successResponse(
            $this->commentService->reactToComment($commentId, $request->input('type'))
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

    public function update(CommentRequest $request, $id)
    {
        try {
            $postId = $this->commentService->updateComment($id, $request->validated())->first()->id;
            $this->forgetCache(self::CACHE_KEY_COMMENTS . $postId);
            
            return $this->successResponse(['message' => 'Comment updated successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update comment', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $postId = $this->commentService->deleteComment($id)->first()->id;
            $this->forgetCache(self::CACHE_KEY_COMMENTS . $postId);
            
            return $this->successResponse(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete comment', 500);
        }
    }
}
