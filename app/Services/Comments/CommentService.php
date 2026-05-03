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
        $post = Post::where('slug', $postId)
            ->firstOrFail();
        if(!$post && is_numeric($postId))
        {
            $post = Post::findOrFail($postId);
        }
        $postId = $post->id;

        return $this->commentRepository->getCommentsForPost($postId, $page, $limit, $sortBy, $order);
    }

    public function fetchCommentById($id)
    {
        $comment = $this->commentRepository->findCommentById($id);
        if (!$comment) {
            throw new Exception('Комментарий не найден.', 404);
        }
        return $comment;
    }

    public function updateComment($id, array $data)
    {
        $comment = $this->commentRepository->findCommentById($id);

        if (! $comment) {
            throw new Exception('Комментарий не найден.', 404);
        }

        if ($comment->user_id !== Auth::id()) {
            throw new Exception('Нет прав на редактирование этого комментария', 403);
        }

        if ($comment->deleted_at) {
            throw new Exception('Нельзя редактировать удалённый комментарий', 400);
        }

        $parentId = $data['parent_id'] ?? $comment->parent_id;

        if ($comment->parent_id && $parentId === null) {
            throw new Exception('Нельзя сделать дочерний комментарий корневым', 400);
        }

        if ($parentId && $parentId == $comment->id) {
            throw new Exception('Комментарий не может быть своим же родителем', 400);
        }

        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (! $parentComment || $parentComment->deleted_at) {
                throw new Exception('Родительский комментарий не найден или удалён', 404);
            }

            if ($parentComment->post_id !== $comment->post_id) {
                throw new Exception('Родительский комментарий принадлежит другому посту', 400);
            }

            $depth = 1;
            $current = $parentComment;
            while ($current->parent_id) {
                $depth++;
                $current = $this->commentRepository->findParentComment($current->parent_id);
                if ($depth > 3) {
                    throw new Exception('Максимальная глубина вложенности комментариев — 3', 400);
                }
            }
        }

        return $this->commentRepository->updateComment($comment, [
            'content' => $data['content'],
            'parent_id' => $parentId,
        ]);
    }

    public function createComment($postId, array $data)
    {
        $post = Post::where('slug', $postId)
            ->firstOrFail();
        if(!$post && is_numeric($postId))
        {
            $post = Post::findOrFail($postId);
        }
        $postId = $post->id;

        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (! $parentComment || $parentComment->deleted_at) {
                throw new Exception('Родительский комментарий не найден или удалён', 404);
            }
            if ($parentComment->post_id !== $postId) {
                throw new Exception('Родительский комментарий принадлежит другому посту', 400);
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
            throw new Exception('Комментарий не найден.', 404);
        }

        return $this->commentRepository->updateOrCreateReaction($commentId, Auth::id(), $type);
    }

    public function reportComment(int $commentId, string $category, string $reason)
    {
        return $this->commentRepository->updateOrCreateReport($commentId, Auth::id(), $category, $reason);
    }

    public function repostComment($commentId)
    {
        return $this->commentRepository->updateOrCreateRepost($commentId, Auth::id());
    }

    public function deleteComment(int $commentId)
    {
        \Log::info('удалитьКомментарий', ['id' => $commentId]);
        $comment = $this->commentRepository->findCommentByIdWithTrashed($commentId);

        if ($comment->user_id !== Auth::id()) {
            throw new Exception('Нет прав на удаление этого комментария', 403);
        }

        $this->cascadeDeleteChildren($comment);

        if($this->commentRepository->deleteComment($comment))
        {
            $this->postStatisticsService->decrementComments($comment->post_id);
        }
        return $comment;
    }

    private function cascadeDeleteChildren($comment)
    {
        foreach ($comment->replies as $child) {
            $this->cascadeDeleteChildren($child);
            $child->delete();
        }
    }
}
