<?php

namespace App\Repositories;

use App\Models\Media\Media;
use Illuminate\Support\Collection;

class MediaRepository
{
    public function create(array $data): Media
    {
        return Media::create($data);
    }

    /**
     * Получает медиа по ID.
     */
    public function getById(int $id): Media
    {
        return Media::findOrFail($id);
    }

    /**
     * Обновляет медиа по ID.
     */
    public function update(int $id, array $data): Media
    {
        $media = $this->getById($id);
        $media->update($data);

        return $media;
    }

    /**
     * Удаляет медиа по ID.
     */
    public function delete(int $id): bool
    {
        $media = $this->getById($id);

        return $media->delete();
    }

    /**
     * Получает все медиа записи пользователя.
     */
    public function getByUserId(int $userId): Collection
    {
        return Media::query()->where('user_id', $userId)->get();
    }

    /**
     * Получает медиа по определённым условиям.
     */
    public function getByConditions(array $conditions): Collection
    {
        return Media::query()->where($conditions)->get();
    }

    /**
     * Удаляет все медиа по определённым условиям.
     *
     * @return int Количество удалённых записей
     */
    public function deleteByConditions(array $conditions): int
    {
        return Media::query()->where($conditions)->delete();
    }

    /**
     * Проверяет, существует ли медиа по заданным условиям.
     */
    public function exists(array $conditions): bool
    {
        return Media::query()->where($conditions)->exists();
    }
}
