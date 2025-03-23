<?php

namespace App\Repositories;

use App\Repositories\Criteria\CriteriaInterface;
use App\Traits\LoggableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Базовый репозиторий для всех репозиториев
 */
abstract class BaseRepository
{
    use LoggableTrait;

    /**
     * Модель, с которой работает репозиторий
     *
     * @var Model|null
     */
    protected ?Model $model = null;

    /**
     * Построитель запросов
     *
     * @var Builder|null
     */
    protected ?Builder $query = null;

    /**
     * Критерии для запросов
     *
     * @var CriteriaInterface[]
     */
    protected array $criteria = [];

    /**
     * Пропустить применение критериев
     *
     * @var bool
     */
    protected bool $skipCriteria = false;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->resetQuery();
        $this->resetCriteria();
        $this->setLogPrefix(strtolower(class_basename($this)));
    }

    /**
     * Получить класс модели
     *
     * @return string
     * @throws Exception
     */
    abstract protected function modelClass(): string;

    /**
     * Получить класс модели (публичный метод)
     *
     * @return string
     */
    public function getModelClass(): string
    {
        try {
            return $this->modelClass();
        } catch (Exception $e) {
            Log::error('Ошибка получения класса модели', [
                'repository' => get_class($this),
                'error' => $e->getMessage()
            ]);

            return Model::class;
        }
    }

    /**
     * Получить новый экземпляр модели
     *
     * @return Model
     */
    protected function makeModel(): Model
    {
        $modelClass = $this->getModelClass();
        return new $modelClass;
    }

    /**
     * Сбросить построитель запросов
     *
     * @return $this
     */
    protected function resetQuery()
    {
        $this->model = $this->makeModel();
        $this->query = $this->model->query();

        return $this;
    }

    /**
     * Сбросить критерии
     *
     * @return $this
     */
    protected function resetCriteria()
    {
        $this->criteria = [];

        return $this;
    }

    /**
     * Получить все записи
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        $this->applyCriteria();
        $result = $this->query->get();
        $this->resetQuery();

        return $result;
    }

    /**
     * Найти запись по ID
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        $this->applyCriteria();
        $result = $this->query->find($id);
        $this->resetQuery();

        return $result;
    }

    /**
     * Создать новую запись
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        $model = $this->model->create($data);
        $this->resetQuery();

        return $model;
    }

    /**
     * Обновить запись
     *
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        $this->resetQuery();

        return $model->fresh();
    }

    /**
     * Удалить запись
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model): bool
    {
        $result = $model->delete();
        $this->resetQuery();

        return $result;
    }

    /**
     * Получить записи с пагинацией
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $this->applyCriteria();
        $result = $this->query->paginate($perPage, $columns, $pageName, $page);
        $this->resetQuery();

        return $result;
    }

    /**
     * Добавить условие where
     *
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @return $this
     */
    public function where(string $column, $value, string $operator = '=')
    {
        $this->query->where($column, $operator, $value);

        return $this;
    }

    /**
     * Добавить условие whereIn
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereIn(string $column, array $values)
    {
        $this->query->whereIn($column, $values);

        return $this;
    }

    /**
     * Загрузить связи
     *
     * @param array|string $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->query->with($relations);

        return $this;
    }

    /**
     * Добавить критерий
     *
     * @param CriteriaInterface $criteria
     * @return $this
     */
    public function pushCriteria(CriteriaInterface $criteria): self
    {
        $this->criteria[] = $criteria;

        return $this;
    }

    /**
     * Получить список критериев
     *
     * @return array
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * Пропустить применение критериев
     *
     * @param bool $status
     * @return $this
     */
    public function skipCriteria(bool $status = true)
    {
        $this->skipCriteria = $status;

        return $this;
    }

    /**
     * Применить критерии к запросу
     *
     * @return $this
     */
    protected function applyCriteria()
    {
        if ($this->skipCriteria) {
            return $this;
        }

        foreach ($this->criteria as $criteria) {
            $this->query = $criteria->apply($this->query);
        }

        return $this;
    }

    /**
     * Получить базовый запрос
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->model->query();
    }

    /**
     * Найти запись по ID с указанными отношениями
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function find(int $id, array $columns = ['*'], array $relations = [])
    {
        $this->applyCriteria();

        if (!empty($relations)) {
            $this->query->with($relations);
        }

        $result = $this->query->find($id, $columns);
        $this->resetQuery();

        return $result;
    }

    /**
     * Получить первую запись, соответствующую условиям
     *
     * @param array $columns
     * @return Model|null
     */
    public function first(array $columns = ['*'])
    {
        $this->applyCriteria();
        $result = $this->query->first($columns);
        $this->resetQuery();

        return $result;
    }

    /**
     * Получить количество записей, соответствующих условиям
     *
     * @return int
     */
    public function count(): int
    {
        $this->applyCriteria();
        $result = $this->query->count();
        $this->resetQuery();

        return $result;
    }

    /**
     * Добавить условие сортировки к запросу
     *
     * @param string $column
     * @param string $direction
     * @return self
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * Очистить критерии поиска
     *
     * @return self
     */
    public function clearCriteria(): self
    {
        $this->criteria = [];

        return $this;
    }
}
