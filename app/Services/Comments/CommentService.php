<?php

namespace App\Services\Comments;

use App\Events\Comments\CommentCreated;
use App\Events\Comments\CommentDeleted;
use App\Events\Comments\CommentUpdated;
use App\Http\Resources\Comments\CommentCollection;
use App\Http\Resources\Comments\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Repositories\Comments\CommentRepository;
use App\Services\Posts\PostStatisticsService;
use App\Services\RepositoryBasedService;
use Illuminate\Support\Facades\Auth;

/**
 * Сервис для работы с комментариями
 */
class CommentService extends RepositoryBasedService
{
    /**
     * Сервис статистики постов
     *
     * @var PostStatisticsService
     */
    protected PostStatisticsService $postStatisticsService;

    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'comments';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 15;

    /**
     * Правила валидации при создании
     *
     * @var array
     */
    protected array $createRules = [
        'content' => 'required|string|max:1000',
        'parent_id' => 'nullable|exists:comments,id',
    ];

    /**
     * Правила валидации при обновлении
     *
     * @var array
     */
    protected array $updateRules = [
        'content' => 'required|string|max:1000',
        'parent_id' => 'nullable|exists:comments,id',
    ];

    /**
     * Конструктор
     *
     * @param CommentRepository $repository
     * @param PostStatisticsService $postStatisticsService
     */
    public function __construct(
        CommentRepository $repository,
        PostStatisticsService $postStatisticsService
    ) {
        parent::__construct($repository);
        $this->postStatisticsService = $postStatisticsService;
        $this->setLogPrefix('CommentService');
    }

    /**
     * Получить комментарии для поста
     *
     * @param int|string $postId ID или slug поста
     * @param int $page Номер страницы
     * @param int $limit Количество комментариев на странице
     * @param string $sortBy Поле для сортировки
     * @param string $order Порядок сортировки
     * @return CommentCollection
     */
    public function getCommentsForPost($postId, int $page = 1, int $limit = 10, string $sortBy = 'created_at', string $order = 'desc'): CommentCollection
    {
        $postId = $this->resolvePostId($postId);
        
        $cacheKey = $this->buildCacheKey('post_comments', [$postId, $page, $limit, $sortBy, $order]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($postId, $page, $limit, $sortBy, $order) {
            $this->logInfo("Получение комментариев для поста ID: {$postId}");
            
            $comments = $this->getRepository()->getCommentsForPost($postId, $page, $limit, $sortBy, $order);
            
            return new CommentCollection($comments);
        });
    }

    /**
     * Обновить комментарий
     *
     * @param int $id ID комментария
     * @param array $data Данные для обновления
     * @return Comment|array
     */
    public function updateComment(int $id, array $data)
    {
        $comment = $this->getRepository()->findCommentById($id);

        if (!$comment) {
            $this->logWarning("Комментарий не найден при попытке обновления", ['comment_id' => $id]);
            return ['message' => 'Комментарий не найден'];
        }

        if (!$this->canUpdate($comment)) {
            $this->logWarning("Отказано в доступе при попытке обновления комментария", ['comment_id' => $id, 'user_id' => Auth::id()]);
            return ['message' => 'У вас нет прав на обновление этого комментария'];
        }

        $this->logInfo("Обновление комментария ID: {$id}");
        
        $this->validate($data, $this->updateRules);
        
        $parentId = $data['parent_id'] ?? null;
        if ($parentId) {
            $parentComment = $this->getRepository()->findParentComment($parentId);
            if (!$parentComment) {
                $this->logWarning("Родительский комментарий не найден", ['parent_id' => $parentId]);
                return ['message' => 'Родительский комментарий не найден'];
            }
        }

        $oldData = $comment->toArray();
        
        $result = $this->transaction(function () use ($comment, $data, $parentId, $oldData) {
            $updatedComment = $this->getRepository()->updateComment($comment, [
                'content' => $data['content'],
                'parent_id' => $parentId,
            ]);
            
            $this->forgetCache($this->buildCacheKey('post_comments', [$comment->post_id]));
            
            event(new CommentUpdated($updatedComment, $oldData));
            
            return $updatedComment;
        });
        
        return $result;
    }

    /**
     * Создать комментарий
     *
     * @param int|string $postId ID или slug поста
     * @param array $data Данные комментария
     * @return Comment|array
     */
    public function createComment($postId, array $data)
    {
        try {
            $postId = $this->resolvePostId($postId);
            
            $this->logInfo("Создание комментария для поста ID: {$postId}");
            
            $this->validate($data, $this->createRules);
            
            $parentId = $data['parent_id'] ?? null;
    
            if ($parentId) {
                $parentComment = $this->getRepository()->findParentComment($parentId);
                if (!$parentComment) {
                    $this->logWarning("Родительский комментарий не найден", ['parent_id' => $parentId]);
                    return ['message' => 'Родительский комментарий не найден'];
                }
            }
    
            $comment = $this->transaction(function () use ($postId, $data, $parentId) {
                $this->postStatisticsService->incrementComments($postId);
    
                $comment = $this->getRepository()->createComment([
                    'post_id' => $postId,
                    'user_id' => Auth::id(),
                    'content' => $data['content'],
                    'parent_id' => $parentId,
                ]);
                
                $this->forgetCache($this->buildCacheKey('post_comments', [$postId]));
                
                event(new CommentCreated($comment));
                
                return $comment;
            });
            
            return $comment;
        } catch (\Exception $e) {
            $this->logError("Ошибка при создании комментария", [
                'post_id' => $postId,
                'error' => $e->getMessage()
            ], $e);
            
            return ['message' => 'Произошла ошибка при создании комментария'];
        }
    }

    /**
     * Добавить реакцию на комментарий
     *
     * @param int $commentId ID комментария
     * @param string $type Тип реакции
     * @return array
     */
    public function reactToComment(int $commentId, string $type): array
    {
        $comment = $this->getRepository()->findCommentById($commentId);

        if (!$comment) {
            $this->logWarning("Комментарий не найден при попытке реакции", ['comment_id' => $commentId]);
            return ['message' => 'Комментарий не найден'];
        }

        $this->logInfo("Добавление реакции типа '{$type}' к комментарию ID: {$commentId}");
        
        $reaction = $this->transaction(function () use ($commentId, $type) {
            $reaction = $this->getRepository()->updateOrCreateReaction($commentId, Auth::id(), $type);
            
            $this->forgetCache($this->buildCacheKey('post_comments', [$reaction->comment->post_id]));
            
            return $reaction;
        });

        return [
            'success' => true, 
            'message' => 'Реакция добавлена', 
            'reaction' => $reaction
        ];
    }

    /**
     * Пожаловаться на комментарий
     *
     * @param int $commentId ID комментария
     * @param string $reason Причина жалобы
     * @return array
     */
    public function reportComment(int $commentId, string $reason): array
    {
        $comment = $this->getRepository()->findCommentById($commentId);

        if (!$comment) {
            $this->logWarning("Комментарий не найден при попытке жалобы", ['comment_id' => $commentId]);
            return ['message' => 'Комментарий не найден'];
        }

        $this->logInfo("Жалоба на комментарий ID: {$commentId}", ['reason' => $reason]);
        
        $report = $this->getRepository()->updateOrCreateReport($commentId, Auth::id(), $reason);

        return [
            'success' => true, 
            'message' => 'Жалоба отправлена', 
            'report' => $report
        ];
    }

    /**
     * Репостнуть комментарий
     *
     * @param int $commentId ID комментария
     * @return array
     */
    public function repostComment(int $commentId): array
    {
        $comment = $this->getRepository()->findCommentById($commentId);

        if (!$comment) {
            $this->logWarning("Комментарий не найден при попытке репоста", ['comment_id' => $commentId]);
            return ['message' => 'Комментарий не найден'];
        }

        $this->logInfo("Репост комментария ID: {$commentId}");
        
        $repost = $this->getRepository()->updateOrCreateRepost($commentId, Auth::id());

        return [
            'success' => true, 
            'message' => 'Репост создан', 
            'repost' => $repost
        ];
    }

    /**
     * Удалить комментарий
     *
     * @param int $commentId ID комментария
     * @return Comment|array
     */
    public function deleteComment(int $commentId)
    {
        $comment = $this->getRepository()->findCommentById($commentId);

        if (!$comment) {
            $this->logWarning("Комментарий не найден при попытке удаления", ['comment_id' => $commentId]);
            return ['message' => 'Комментарий не найден'];
        }

        if (!$this->canDelete($comment)) {
            $this->logWarning("Отказано в доступе при попытке удаления комментария", ['comment_id' => $commentId, 'user_id' => Auth::id()]);
            return ['message' => 'У вас нет прав на удаление этого комментария'];
        }

        $this->logInfo("Удаление комментария ID: {$commentId}");
        
        $this->transaction(function () use ($comment) {
            $this->postStatisticsService->decrementComments($comment->post_id);
            
            // Сохраняем копию для события
            $commentCopy = clone $comment;
            
            $this->getRepository()->deleteComment($comment);
            
            $this->forgetCache($this->buildCacheKey('post_comments', [$comment->post_id]));
            
            event(new CommentDeleted($commentCopy));
        });

        return $comment;
    }

    /**
     * Получить ID поста по ID или slug
     *
     * @param int|string $postId ID или slug поста
     * @return int
     */
    protected function resolvePostId($postId): int
    {
        if (is_numeric($postId)) {
            return Post::findOrFail($postId)->id;
        } else {
            return Post::where('slug', $postId)->firstOrFail()->id;
        }
    }

    /**
     * Проверить, может ли текущий пользователь редактировать комментарий
     *
     * @param \Illuminate\Database\Eloquent\Model $comment
     * @return bool
     */
    protected function canUpdate(\Illuminate\Database\Eloquent\Model $comment): bool
    {
        // Проверка, что модель - это комментарий
        if (!($comment instanceof Comment)) {
            return false;
        }
        
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Автор комментария или администратор может редактировать
        return $user->id === $comment->user_id || $user->hasRole('admin');
    }

    /**
     * Проверить, может ли текущий пользователь удалить комментарий
     *
     * @param \Illuminate\Database\Eloquent\Model $comment
     * @return bool
     */
    protected function canDelete(\Illuminate\Database\Eloquent\Model $comment): bool
    {
        // Проверка, что модель - это комментарий
        if (!($comment instanceof Comment)) {
            return false;
        }
        
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Автор комментария, автор поста или администратор может удалять
        return $user->id === $comment->user_id || 
               $user->id === $comment->post->user_id ||
               $user->hasRole('admin');
    }

    /**
     * Получить класс события для создания
     *
     * @return string
     */
    protected function getCreateEventClass(): string
    {
        return CommentCreated::class;
    }

    /**
     * Получить класс события для обновления
     *
     * @return string
     */
    protected function getUpdateEventClass(): string
    {
        return CommentUpdated::class;
    }

    /**
     * Получить класс события для удаления
     *
     * @return string
     */
    protected function getDeleteEventClass(): string
    {
        return CommentDeleted::class;
    }
}
