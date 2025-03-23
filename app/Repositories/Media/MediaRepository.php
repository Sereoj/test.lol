<?php

namespace App\Repositories\Media;

use App\Models\Media\Media;
use App\Repositories\BaseRepository;

/**
 * Репозиторий для работы с медиафайлами
 */
class MediaRepository extends BaseRepository
{
    /**
     * Возвращает модель
     *
     * @return string
     */
    public function model(): string
    {
        return Media::class;
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
        $this->logInfo("Поиск медиафайла с ID: {$id}");
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Получить медиафайл по ID
     *
     * @param int $id ID медиафайла
     * @return Media|null
     */
    public function getById(int $id): ?Media
    {
        return $this->model->find($id);
    }

    /**
     * Обновить медиафайл
     *
     * @param int $id ID медиафайла
     * @param array $data Данные для обновления
     * @return Media|null
     */
    public function update(int $id, array $data): ?Media
    {
        $media = $this->getById($id);

        if (!$media) {
            return null;
        }

        $media->update($data);
        return $media->fresh();
    }

    /**
     * Создать медиафайл
     *
     * @param array $data Данные медиафайла
     * @return Media
     */
    public function create(array $data): Media
    {
        return $this->model->create($data);
    }

    /**
     * Удалить медиафайл
     *
     * @param int $id ID медиафайла
     * @return bool
     */
    public function delete(int $id): bool
    {
        $media = $this->getById($id);

        if (!$media) {
            return false;
        }

        return $media->delete();
    }

    /**
     * Получить медиафайлы пользователя
     *
     * @param int $userId ID пользователя
     * @param int $page Номер страницы
     * @param int $limit Количество на странице
     * @param string $type Тип медиафайла (image, video, document)
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserMedia(int $userId, int $page = 1, int $limit = 10, ?string $type = null)
    {
        $query = $this->model->where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Получить публичные медиафайлы
     *
     * @param int $page Номер страницы
     * @param int $limit Количество на странице
     * @param string $type Тип медиафайла (image, video, document)
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPublicMedia(int $page = 1, int $limit = 10, ?string $type = null)
    {
        $query = $this->model->where('is_public', true);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
    }
}
