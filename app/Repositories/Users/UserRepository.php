<?php

namespace App\Repositories\Users;

use App\Models\Users\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * Репозиторий для работы с пользователями
 */
class UserRepository extends BaseRepository
{
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
        $this->logInfo("Поиск пользователя с ID: {$id}");
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Найти пользователя по email
     *
     * @param string $email
     * @return Model|null
     */
    public function findByEmail(string $email): ?Model
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Получить пользователей, которые следуют за указанным пользователем
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowers(int $userId)
    {
        return $this->model->whereHas('following', function ($query) use ($userId) {
            $query->where('following_id', $userId);
        })->get();
    }

    /**
     * Получить пользователей, за которыми следует указанный пользователь
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowing(int $userId)
    {
        return $this->model->whereHas('followers', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    /**
     * Проверить, следует ли пользователь за другим пользователем
     *
     * @param int $userId ID пользователя
     * @param int $followingId ID пользователя, за которым может следить
     * @return bool
     */
    public function isFollowing(int $userId, int $followingId): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        return $user->following()->where('following_id', $followingId)->exists();
    }

    /**
     * Создать запись о подписке на пользователя
     *
     * @param int $userId ID пользователя
     * @param int $followingId ID пользователя, на которого подписываемся
     * @return bool
     */
    public function follow(int $userId, int $followingId): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        // Проверяем, не подписан ли уже
        if ($this->isFollowing($userId, $followingId)) {
            return true;
        }

        $user->following()->attach($followingId);
        return true;
    }

    /**
     * Удалить запись о подписке на пользователя
     *
     * @param int $userId ID пользователя
     * @param int $followingId ID пользователя, от которого отписываемся
     * @return bool
     */
    public function unfollow(int $userId, int $followingId): bool
    {
        $user = $this->findById($userId);

        if (!$user) {
            return false;
        }

        $user->following()->detach($followingId);
        return true;
    }

    /**
     * Получить количество подписчиков пользователя
     *
     * @param int $userId ID пользователя
     * @return int
     */
    public function getFollowersCount(int $userId): int
    {
        $user = $this->findById($userId);

        if (!$user) {
            return 0;
        }

        return $user->followers()->count();
    }

    /**
     * Получить количество подписок пользователя
     *
     * @param int $userId ID пользователя
     * @return int
     */
    public function getFollowingCount(int $userId): int
    {
        $user = $this->findById($userId);

        if (!$user) {
            return 0;
        }

        return $user->following()->count();
    }
}
