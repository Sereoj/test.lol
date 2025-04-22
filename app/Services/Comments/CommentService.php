<?php

namespace App\Services\Comments;

use App\Models\Comments\Comment;
use App\Models\Posts\Post;
use App\Repositories\CommentRepository;
use App\Services\Posts\PostStatisticsService;
use Exception;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    protected CommentRepository $commentRepository;

    protected PostStatisticsService $postStatisticsService;

    public function __construct(
        CommentRepository $commentRepository,
        PostStatisticsService $postStatisticsService,
    ) {
        $this->commentRepository = $commentRepository;
        $this->postStatisticsService = $postStatisticsService;
    }

    public function fetchCommentsForPost($postId, $page = 1, $limit = 10, $sortBy = 'created_at', $order = 'desc')
    {
        $post = is_numeric($postId)
            ? Post::findOrFail($postId)
            : Post::where('slug', $postId)->firstOrFail();
        $postId = $post->id;

        return $this->commentRepository->getCommentsForPost($postId, $page, $limit, $sortBy, $order);
    }

    public function fetchCommentById($id)
    {
        $comment = $this->commentRepository->findCommentById($id);
        if (!$comment) {
            throw new Exception('Comment not found.', 404);
        }
        return $comment;
    }

    public function updateComment($id, array $data)
    {
        $comment = $this->commentRepository->findCommentById($id);

        if (! $comment) {
            throw new Exception('Comment not found.', 404);
        }

        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (! $parentComment) {
                throw new Exception('Parent comment not found.', 404);
            }
        }

        return $this->commentRepository->updateComment($comment, [
            'content' => $data['content'],
            'parent_id' => $parentId,
        ]);
    }

    public function createComment($postId, array $data)
    {
        $post = is_numeric($postId)
            ? Post::findOrFail($postId)
            : Post::where('slug', $postId)->firstOrFail();

        $postId = $post->id;
        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (! $parentComment) {
                throw new Exception('Parent comment not found.', 404);
            }
        }

        $this->postStatisticsService->incrementComments($postId);

        return $this->commentRepository->createComment([
            'post_id' => $postId,
            'user_id' => Auth::id(),
            'content' => $data['content'],
            'parent_id' => $parentId,
        ]);
    }

    public function reactToComment($commentId, $type)
    {
        $comment = $this->commentRepository->findCommentById($commentId);

        if (! $comment) {
            throw new Exception('Comment not found.', 404);
        }

        return $this->commentRepository->updateOrCreateReaction($commentId, Auth::id(), $type);
    }

    public function reportComment($commentId, $reason)
    {
        return $this->commentRepository->updateOrCreateReport($commentId, Auth::id(), $reason);
    }

    public function repostComment($commentId)
    {
        return $this->commentRepository->updateOrCreateRepost($commentId, Auth::id());
    }

    public function deleteComment(int $commentId)
    {
        \Log::info('deleteComment', ['id' => $commentId]);
        $comment = $this->commentRepository->findCommentByIdWithTrashed($commentId);

        if($this->commentRepository->deleteComment($comment))
        {
            $this->postStatisticsService->decrementComments($comment->post_id);
        }
        return $comment;
    }
}
