<?php

namespace App\Repositories\Avatar;

use App\Models\Media\Avatar;
use App\Repositories\BaseRepository;

/**
 * Репозиторий для работы с аватарами пользователя
 */
class AvatarRepository extends BaseRepository
{
    /**
     * Возвращает модель
     *
     * @return string
     */
    public function model(): string
    {
        return Avatar::class;
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
        $this->logInfo("Поиск аватара с ID: {$id}");
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Создать аватар
     *
     * @param array $data Данные аватара
     * @return Avatar
     */
    public function createAvatar(array $data): Avatar
    {
        return $this->model->create($data);
    }

    /**
     * Получить все аватары пользователя
     *
     * @param int $userId ID пользователя
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserAvatars(int $userId)
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Удалить аватар
     *
     * @param Avatar $avatar Аватар для удаления
     * @return bool|null
     */
    public function deleteAvatar(Avatar $avatar): ?bool
    {
        return $avatar->delete();
    }

    /**
     * Найти аватар по ID пользователя и ID аватара
     *
     * @param int $userId ID пользователя
     * @param int $avatarId ID аватара
     * @return Avatar|null
     */
    public function findAvatarByUserIdAndId(int $userId, int $avatarId): ?Avatar
    {
        return $this->model->where('user_id', $userId)
            ->where('id', $avatarId)
            ->first();
    }

    /**
     * Получить последний аватар пользователя
     *
     * @param int $userId ID пользователя
     * @return Avatar|null
     */
    public function getLatestAvatar(int $userId): ?Avatar
    {
        return $this->model->where('user_id', $userId)
            ->latest()
            ->first();
    }

    /**
     * Отметить все предыдущие аватары пользователя как неактивные
     *
     * @param int $userId ID пользователя
     * @return bool
     */
    public function deactivatePreviousAvatars(int $userId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Активировать определенный аватар пользователя
     *
     * @param int $userId ID пользователя
     * @param int $avatarId ID аватара
     * @return bool
     */
    public function activateAvatar(int $userId, int $avatarId): bool
    {
        $this->deactivatePreviousAvatars($userId);

        return $this->model->where('user_id', $userId)
            ->where('id', $avatarId)
            ->update(['is_active' => true]);
    }
}
