<?php

namespace App\Repositories\Comments;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\CommentReport;
use App\Models\CommentRepost;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Репозиторий для работы с комментариями
 */
class CommentRepository extends BaseRepository
{
    /**
     * Указать модель
     *
     * @return string
     */
    public function model(): string
    {
        return Comment::class;
    }

    /**
     * Установить модель
     *
     * @return void
     */
    protected function setModel(): void
    {
        $this->model = app()->make($this->model());
    }

    /**
     * Найти запись по ID с указанными отношениями
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(int $id, array $columns = ['*'], array $relations = [])
    {
        $this->logInfo("Поиск комментария с ID: {$id}");
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Получить комментарии для поста с пагинацией
     *
     * @param int $postId ID поста
     * @param int $page Номер страницы
     * @param int $limit Количество комментариев на странице
     * @param string $sortBy Поле для сортировки
     * @param string $order Порядок сортировки
     * @return LengthAwarePaginator
     */
    public function getCommentsForPost(int $postId, int $page = 1, int $limit = 10, string $sortBy = 'created_at', string $order = 'desc'): LengthAwarePaginator
    {
        return $this->model
            ->where('post_id', $postId)
            ->whereNull('parent_id') // Только корневые комментарии
            ->with(['user', 'replies.user', 'reactions'])
            ->orderBy($sortBy, $order)
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Найти комментарий по ID
     *
     * @param int $id ID комментария
     * @return Comment|null
     */
    public function findCommentById(int $id): ?Comment
    {
        return $this->find($id);
    }

    /**
     * Найти родительский комментарий
     *
     * @param int $parentId ID родительского комментария
     * @return Comment|null
     */
    public function findParentComment(int $parentId): ?Comment
    {
        return $this->find($parentId);
    }

    /**
     * Создать комментарий
     *
     * @param array $data Данные комментария
     * @return Comment
     */
    public function createComment(array $data): Comment
    {
        return $this->create($data);
    }

    /**
     * Обновить комментарий
     *
     * @param Comment $comment Комментарий
     * @param array $data Новые данные
     * @return Comment
     */
    public function updateComment(Comment $comment, array $data): Comment
    {
        $this->update($data, $comment->id);
        return $comment->refresh();
    }

    /**
     * Удалить комментарий
     *
     * @param Comment $comment Комментарий
     * @return bool
     */
    public function deleteComment(Comment $comment): bool
    {
        return $this->delete($comment->id);
    }

    /**
     * Обновить или создать реакцию на комментарий
     *
     * @param int $commentId ID комментария
     * @param int $userId ID пользователя
     * @param string $type Тип реакции
     * @return CommentReaction
     */
    public function updateOrCreateReaction(int $commentId, int $userId, string $type): CommentReaction
    {
        return CommentReaction::updateOrCreate(
            ['comment_id' => $commentId, 'user_id' => $userId],
            ['type' => $type]
        );
    }

    /**
     * Обновить или создать жалобу на комментарий
     *
     * @param int $commentId ID комментария
     * @param int $userId ID пользователя
     * @param string $reason Причина жалобы
     * @return CommentReport
     */
    public function updateOrCreateReport(int $commentId, int $userId, string $reason): CommentReport
    {
        return CommentReport::updateOrCreate(
            ['comment_id' => $commentId, 'user_id' => $userId],
            ['reason' => $reason]
        );
    }

    /**
     * Обновить или создать репост комментария
     *
     * @param int $commentId ID комментария
     * @param int $userId ID пользователя
     * @return CommentRepost
     */
    public function updateOrCreateRepost(int $commentId, int $userId): CommentRepost
    {
        return CommentRepost::updateOrCreate(
            ['comment_id' => $commentId, 'user_id' => $userId]
        );
    }

    /**
     * Получить все реакции для комментария
     *
     * @param int $commentId ID комментария
     * @return Collection
     */
    public function getReactionsForComment(int $commentId): Collection
    {
        return CommentReaction::where('comment_id', $commentId)->get();
    }

    /**
     * Получить все ответы для комментария
     *
     * @param int $commentId ID комментария
     * @return Collection
     */
    public function getRepliesForComment(int $commentId): Collection
    {
        return $this->model->where('parent_id', $commentId)->with('user')->get();
    }
}