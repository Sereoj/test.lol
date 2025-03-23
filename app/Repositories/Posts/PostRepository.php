<?php

namespace App\Repositories\Posts;

use App\Models\Posts\Post;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Репозиторий для работы с постами
 */
class PostRepository extends BaseRepository
{
    /**
     * Указать модель
     *
     * @return string
     */
    public function model(): string
    {
        return Post::class;
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
        $this->logInfo("Поиск поста с ID: {$id}");
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Получить посты с пагинацией
     *
     * @param array $criteria
     * @param array $columns
     * @param array $relations
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPosts(array $criteria = [], array $columns = ['*'], array $relations = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with($relations)
            ->where($criteria)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, $columns);
    }

    /**
     * Найти пост по слагу
     *
     * @param string $slug
     * @param array $columns
     * @param array $relations
     */
    public function findBySlug(string $slug, array $columns = ['*'], array $relations = []): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model
            ->with($relations)
            ->where('slug', $slug)
            ->first($columns);
    }

    /**
     * Получить посты пользователя
     *
     * @param int $userId
     * @param array $columns
     * @param array $relations
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserPosts(int $userId, array $columns = ['*'], array $relations = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with($relations)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, $columns);
    }

    /**
     * Поставить лайк посту
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function likePost(int $postId, int $userId): bool
    {
        $post = $this->find($postId);
        if (!$post) {
            return false;
        }

        if (!$post->likes()->where('user_id', $userId)->exists()) {
            $post->likes()->create(['user_id' => $userId]);
            $post->increment('likes_count');
        }

        return true;
    }

    /**
     * Убрать лайк с поста
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function unlikePost(int $postId, int $userId): bool
    {
        $post = $this->find($postId);
        if (!$post) {
            return false;
        }

        $deleted = $post->likes()->where('user_id', $userId)->delete();
        if ($deleted) {
            $post->decrement('likes_count');
        }

        return (bool)$deleted;
    }

    /**
     * Проверить, лайкнул ли пользователь пост
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function isLiked(int $postId, int $userId): bool
    {
        $post = $this->find($postId);
        if (!$post) {
            return false;
        }

        return $post->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Репостнуть пост
     *
     * @param int $postId
     * @param int $userId
     * @param string|null $comment
     * @return Post|null
     */
    public function repostPost(int $postId, int $userId, ?string $comment = null): ?Post
    {
        $originalPost = $this->find($postId);
        if (!$originalPost) {
            return null;
        }

        $repost = $this->model->create([
            'user_id' => $userId,
            'content' => $comment ?? '',
            'original_post_id' => $postId,
            'is_repost' => true,
        ]);

        $originalPost->increment('reposts_count');

        return $repost;
    }

    /**
     * Получить посты, которые являются репостами указанного поста
     *
     * @param int $postId
     * @param array $columns
     * @return Collection
     */
    public function getRepostsOf(int $postId, array $columns = ['*']): Collection
    {
        return $this->model
            ->where('original_post_id', $postId)
            ->where('is_repost', true)
            ->get($columns);
    }

    /**
     * Получить вложенные медиафайлы поста
     *
     * @param int $postId
     * @return \Illuminate\Support\Collection
     */
    public function getPostMedia(int $postId): \Illuminate\Support\Collection
    {
        $post = $this->find($postId);
        if (!$post) {
            return collect();
        }

        return $post->media;
    }
}
