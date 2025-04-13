<?php

namespace App\Services\Comments;

use App\Formatters\CommentFormatter;
use App\Http\Resources\Comments\CommentCollection;
use App\Http\Resources\Comments\CommentResource;
use App\Models\Posts\Post;
use App\Repositories\CommentRepository;
use App\Services\Posts\PostStatisticsService;
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

    public function getCommentsForPost($postId, $page = 1, $limit = 10, $sortBy = 'created_at', $order = 'desc')
    {
        if (is_numeric($postId)) {
            $postId = Post::findOrFail($postId)->id;
        }else{
            $postId = Post::where('slug', $postId)->firstOrFail()->id;
        }

        $rawComments = $this->commentRepository->getCommentsForPost($postId, $page, $limit, $sortBy, $order);
        return new CommentCollection($rawComments);
    }

    public function updateComment($id, array $data)
    {
        $comment = $this->commentRepository->findCommentById($id);

        if (! $comment) {
            return ['message' => 'Comment not found.'];
        }

        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (! $parentComment) {
                return ['message' => 'Parent comment not found.'];
            }
        }

        return $this->commentRepository->updateComment($comment, [
            'content' => $data['content'],
            'parent_id' => $parentId,
        ]);
    }

    public function createComment($postId, array $data)
    {
        if (is_numeric($postId)) {
            $postId = Post::findOrFail($postId)->id;
        }else{
            $postId = Post::where('slug', $postId)->firstOrFail()->id;
        }

        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (! $parentComment) {
                return ['message' => 'Parent comment not found.'];
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
            return ['message' => 'Comment not found.'];
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

    public function deleteComment($commentId)
    {
        $comment = $this->commentRepository->findCommentById($commentId);

        if (! $comment) {
            return ['message' => 'Comment not found.'];
        }

        $this->postStatisticsService->decrementComments($comment->post_id);

        $this->commentRepository->deleteComment($comment);

        return $comment;
    }
}
