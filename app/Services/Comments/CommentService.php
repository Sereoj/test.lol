<?php

namespace App\Services\Comments;

use App\Formatters\CommentFormatter;
use App\Http\Resources\Comments\CommentCollection;
use App\Http\Resources\Comments\CommentResource;
use App\Models\Posts\Post;
use App\Repositories\CommentRepository;
use App\Services\Posts\PostStatisticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

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

    /**
     * Получить комментарии для поста с пагинацией
     */
    public function getCommentsForPost($postId, $page = 1, $limit = 10, $sortBy = 'created_at', $order = 'desc')
    {
        if (is_numeric($postId)) {
            $postId = Post::findOrFail($postId)->id;
        } else {
            $postId = Post::where('slug', $postId)->firstOrFail()->id;
        }

        $rawComments = $this->commentRepository->getCommentsForPost($postId, $page, $limit, $sortBy, $order);
        return new CommentCollection($rawComments);
    }

    /**
     * Получить ответы на комментарий
     */
    public function getCommentReplies($commentId, array $options = [], $userId = null)
    {
        $page = $options['page'] ?? 1;
        $limit = $options['per_page'] ?? 10;
        
        $replies = $this->commentRepository->getCommentReplies($commentId, $page, $limit);
        
        // Если коллекция не является LengthAwarePaginator, преобразуем её
        if (!($replies instanceof LengthAwarePaginator)) {
            $total = count($replies);
            $items = $replies->forPage($page, $limit);
            $replies = new LengthAwarePaginator(
                $items, 
                $total, 
                $limit, 
                $page, 
                ['path' => request()->url()]
            );
        }
        
        return new CommentCollection($replies);
    }

    /**
     * Получить комментарий по ID
     * 
     * @param int $commentId ID комментария
     * @return mixed Комментарий или null
     */
    public function findCommentById($commentId)
    {
        return $this->commentRepository->findCommentById($commentId);
    }

    /**
     * Обновить комментарий
     */
    public function updateComment($id, array $data)
    {
        $comment = $this->commentRepository->findCommentById($id);

        if (!$comment) {
            return ['message' => 'Comment not found.'];
        }

        // Проверка прав пользователя на редактирование комментария
        if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return ['message' => 'You do not have permission to edit this comment.'];
        }

        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (!$parentComment) {
                return ['message' => 'Parent comment not found.'];
            }
        }

        return $this->commentRepository->updateComment($comment, [
            'content' => $data['content'],
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Создать новый комментарий
     */
    public function createComment($postId, array $data)
    {
        if (is_numeric($postId)) {
            try {
                $post = Post::findOrFail($postId);
                $postId = $post->id;
            } catch (\Exception $e) {
                return ['message' => 'Post not found.'];
            }
        } else {
            $post = Post::where('slug', $postId)->first();
            if (!$post) {
                return ['message' => 'Post not found.'];
            }
            $postId = $post->id;
        }

        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            $parentComment = $this->commentRepository->findParentComment($parentId);
            if (!$parentComment) {
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

    /**
     * Добавить/удалить реакцию на комментарий
     */
    public function reactToComment($commentId, $type)
    {
        $comment = $this->commentRepository->findCommentById($commentId);

        if (!$comment) {
            return ['message' => 'Comment not found.'];
        }

        $result = $this->commentRepository->updateOrCreateReaction($commentId, Auth::id(), $type);
        
        // Обновляем счетчик лайков после операции
        $comment = $this->commentRepository->findCommentById($commentId);
        
        return [
            'success' => true,
            'likes_count' => $comment->likes_count ?? 0
        ];
    }

    /**
     * Лайкнуть комментарий (устаревший метод)
     */
    public function likeComment($commentId)
    {
        return $this->reactToComment($commentId, 'like');
    }

    /**
     * Снять лайк с комментария (устаревший метод)
     */
    public function unlikeComment($commentId)
    {
        return $this->reactToComment($commentId, 'dislike');
    }

    /**
     * Пожаловаться на комментарий
     */
    public function reportComment($commentId, $reason)
    {
        $comment = $this->commentRepository->findCommentById($commentId);

        if (!$comment) {
            return ['message' => 'Comment not found.'];
        }

        return $this->commentRepository->updateOrCreateReport($commentId, Auth::id(), $reason);
    }

    /**
     * Репостнуть комментарий
     */
    public function repostComment($commentId)
    {
        $comment = $this->commentRepository->findCommentById($commentId);

        if (!$comment) {
            return ['message' => 'Comment not found.'];
        }

        return $this->commentRepository->updateOrCreateRepost($commentId, Auth::id());
    }

    /**
     * Удалить комментарий
     */
    public function deleteComment($commentId)
    {
        $comment = $this->commentRepository->findCommentById($commentId);

        if (!$comment) {
            return ['message' => 'Comment not found.'];
        }

        // Проверка прав пользователя на удаление комментария
        if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return ['message' => 'You do not have permission to delete this comment.'];
        }

        $this->postStatisticsService->decrementComments($comment->post_id);

        $this->commentRepository->deleteComment($comment);

        return $comment;
    }
}
