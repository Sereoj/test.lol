<?php

namespace App\Services;

use App\Interfaces\RepositoryServiceInterface;
use App\Repositories\RepositoryInterface;
use App\Traits\RepositoryAwareTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use App\Repositories\PostRepository;

/**
 * Базовый класс для сервисов, использующих репозитории
 */
abstract class RepositoryBasedService extends BaseService implements RepositoryServiceInterface
{
    use RepositoryAwareTrait;

    /**
     * Класс события создания
     *
     * @var string|null
     */
    protected ?string $createEventClass = null;

    /**
     * Класс события обновления
     *
     * @var string|null
     */
    protected ?string $updateEventClass = null;

    /**
     * Класс события удаления
     *
     * @var string|null
     */
    protected ?string $deleteEventClass = null;

    /**
     * Конструктор
     *
     * @param RepositoryInterface|null $repository
     */
    public function __construct(?RepositoryInterface $repository = null)
    {
        parent::__construct();

        if ($repository !== null) {
            $this->setRepository($repository);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelClass(): ?string
    {
        $modelClass = $this->getRepositoryModelClass();
        return $modelClass ?? parent::getModelClass();
    }

    /**
     * Создать модель из данных
     *
     * @param array $data
     * @return Model|null
     */
    protected function createModel(array $data): ?Model
    {
        if (!$this->hasRepository()) {
            $this->logError('Невозможно создать модель: репозиторий не инициализирован');
            return null;
        }

        return $this->callRepository('create', [$data]);
    }

    /**
     * Обновить модель из данных
     *
     * @param Model $model
     * @param array $data
     * @return Model|null
     */
    protected function updateModel(Model $model, array $data): ?Model
    {
        if (!$this->hasRepository()) {
            $this->logError('Невозможно обновить модель: репозиторий не инициализирован');
            return null;
        }

        return $this->callRepository('update', [$model, $data], $model);
    }

    /**
     * Удалить модель
     *
     * @param Model $model
     * @return bool
     */
    protected function deleteModel(Model $model): bool
    {
        if (!$this->hasRepository()) {
            $this->logError('Невозможно удалить модель: репозиторий не инициализирован');
            return false;
        }

        return $this->callRepository('delete', [$model], false);
    }

    /**
     * Найти модель по ID
     *
     * @param int $id
     * @return Model|null
     */
    protected function findModel(int $id): ?Model
    {
        if (!$this->hasRepository()) {
            $this->logWarning('Невозможно найти модель: репозиторий не инициализирован');
            return null;
        }

        return $this->callRepository('findById', [$id]);
    }

    /**
     * Создать новую запись
     *
     * @param array $data
     * @return Model|null
     */
    public function create(array $data): ?Model
    {
        if (!$this->hasRepository()) {
            $this->logError('Невозможно создать запись: репозиторий не инициализирован');
            return null;
        }

        $this->withContext('operation', 'create');

        try {
            // Валидируем данные
            $validData = $this->validate($data, $this->validationRules);

            // Выполняем в транзакции
            return $this->transaction(function () use ($validData) {
                // Создаем модель
                $model = $this->createModel($validData);

                if ($model) {
                    // Очищаем кеш
                    $this->clearCacheForModel($model->id);

                    // Диспатчим событие
                    $this->dispatchCreateEvent($model);

                    $this->logInfo("Создана запись {$this->getModelName()}", [
                        'id' => $model->id
                    ]);
                }

                return $model;
            });
        } catch (Exception $e) {
            $this->logError("Ошибка при создании записи {$this->getModelName()}", [
                'data' => $this->maskSensitiveData($data)
            ], $e);

            throw $e;
        }
    }

    /**
     * Диспатчить событие создания модели
     *
     * @param Model $model
     * @return void
     */
    protected function dispatchCreateEvent(Model $model): void
    {
        $eventClass = $this->getCreateEventClass();

        if ($eventClass && class_exists($eventClass)) {
            $this->dispatchIf(new $eventClass($model));
        }
    }

    /**
     * Обновить запись
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(int $id, array $data): ?Model
    {
        if (!$this->hasRepository()) {
            $this->logError('Невозможно обновить запись: репозиторий не инициализирован');
            return null;
        }

        $this->withContext('operation', 'update')
             ->withContext('id', $id);

        // Находим модель
        $model = $this->findById($id);

        if (!$model) {
            $this->logWarning("Запись {$this->getModelName()} не найдена для обновления", ['id' => $id]);
            return null;
        }

        // Проверяем права доступа
        if (!$this->canUpdate($model)) {
            $this->logWarning("Отказано в доступе на обновление {$this->getModelName()}", ['id' => $id]);
            return null;
        }

        try {
            // Валидируем данные с правилами обновления
            $rules = $this->updateValidationRules ?: $this->validationRules;
            $validData = $this->validate($data, $rules);

            // Сохраняем старые данные для события
            $oldData = $model->toArray();

            // Выполняем в транзакции
            return $this->transaction(function () use ($model, $validData, $oldData) {
                // Обновляем модель
                $updated = $this->updateModel($model, $validData);

                if ($updated) {
                    // Очищаем кеш
                    $this->clearCacheForModel($updated->id);

                    // Диспатчим событие
                    $this->dispatchUpdateEvent($updated, $oldData);

                    $this->logInfo("Обновлена запись {$this->getModelName()}", [
                        'id' => $updated->id
                    ]);
                }

                return $updated;
            });
        } catch (Exception $e) {
            $this->logError("Ошибка при обновлении записи {$this->getModelName()}", [
                'id' => $id,
                'data' => $this->maskSensitiveData($data)
            ], $e);

            throw $e;
        }
    }

    /**
     * Диспатчить событие обновления модели
     *
     * @param Model $model
     * @param array $oldData
     * @return void
     */
    protected function dispatchUpdateEvent(Model $model, array $oldData): void
    {
        $eventClass = $this->getUpdateEventClass();

        if ($eventClass && class_exists($eventClass)) {
            $this->dispatchIf(new $eventClass($model, $oldData));
        }
    }

    /**
     * Удалить запись
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        if (!$this->hasRepository()) {
            $this->logError('Невозможно удалить запись: репозиторий не инициализирован');
            return false;
        }

        $this->withContext('operation', 'delete')
             ->withContext('id', $id);

        // Находим модель
        $model = $this->findById($id);

        if (!$model) {
            $this->logWarning("Запись {$this->getModelName()} не найдена для удаления", ['id' => $id]);
            return false;
        }

        // Проверяем права доступа
        if (!$this->canDelete($model)) {
            $this->logWarning("Отказано в доступе на удаление {$this->getModelName()}", ['id' => $id]);
            return false;
        }

        try {
            // Выполняем в транзакции
            return $this->transaction(function () use ($model) {
                // Удаляем модель
                $result = $this->deleteModel($model);

                if ($result) {
                    // Очищаем кеш
                    $this->clearCacheForModel($model->id);

                    // Диспатчим событие
                    $this->dispatchDeleteEvent($model);

                    $this->logInfo("Удалена запись {$this->getModelName()}", [
                        'id' => $model->id
                    ]);
                }

                return $result;
            });
        } catch (Exception $e) {
            $this->logError("Ошибка при удалении записи {$this->getModelName()}", [
                'id' => $id
            ], $e);

            throw $e;
        }
    }

    /**
     * Диспатчить событие удаления модели
     *
     * @param Model $model
     * @return void
     */
    protected function dispatchDeleteEvent(Model $model): void
    {
        $eventClass = $this->getDeleteEventClass();

        if ($eventClass && class_exists($eventClass)) {
            $this->dispatchIf(new $eventClass($model));
        }
    }

    /**
     * Найти запись по ID
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        if (!$this->hasRepository()) {
            $this->logWarning('Невозможно найти запись: репозиторий не инициализирован');
            return null;
        }

        $cacheKey = $this->getCacheKeyForModel($id);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $model = $this->findModel($id);

            if ($model) {
                $this->logInfo("Найдена запись {$this->getModelName()}", ['id' => $id]);
            } else {
                $this->logInfo("Запись {$this->getModelName()} не найдена", ['id' => $id]);
            }

            return $model;
        });
    }

    /**
     * Получить все записи
     *
     * @param array $relations Связи для загрузки
     * @return Collection
     */
    public function getAll(array $relations = [])
    {
        if (!$this->hasRepository()) {
            $this->logWarning('Невозможно получить все записи: репозиторий не инициализирован');
            return collect();
        }

        $cacheKey = $this->getCacheKeyForList(['relations' => $relations]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($relations) {
            $repository = $this->getRepository();

            if (!$repository) {
                return collect();
            }

            if (!empty($relations) && method_exists($repository, 'with')) {
                $repository->with($relations);
            }

            $result = $repository->getAll();

            $this->logInfo("Получен список записей {$this->getModelName()}", [
                'count' => $result->count(),
                'relations' => $relations
            ]);

            return $result;
        });
    }

    /**
     * Получить записи с пагинацией
     *
     * @param array $filters Фильтры
     * @param int $perPage Количество записей на странице
     * @param array $columns Колонки для выборки
     * @param string $pageName Имя параметра страницы
     * @param int|null $page Номер страницы
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        if (!$this->hasRepository()) {
            $this->logWarning('Невозможно выполнить пагинацию: репозиторий не инициализирован');
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $cacheKey = $this->buildCacheKey('paginate', [
            'filters' => $filters,
            'perPage' => $perPage,
            'columns' => $columns,
            'pageName' => $pageName,
            'page' => $page
        ]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($filters, $perPage, $columns, $pageName, $page) {
            $repository = $this->getRepository();

            if (!$repository) {
                return new LengthAwarePaginator([], 0, $perPage);
            }

            // Применяем фильтры
            foreach ($filters as $field => $value) {
                if (is_array($value) && method_exists($repository, 'whereIn')) {
                    $repository->whereIn($field, $value);
                } elseif (method_exists($repository, 'where')) {
                    $repository->where($field, $value);
                }
            }

            $result = $repository->paginate($perPage, $columns, $pageName, $page);

            $this->logInfo("Получены записи {$this->getModelName()} с пагинацией", [
                'total' => $result->total(),
                'per_page' => $perPage,
                'current_page' => $result->currentPage()
            ]);

            return $result;
        });
    }

    /**
     * Очистить кеш для модели
     *
     * @param int $id
     * @return void
     */
    protected function clearCacheForModel(int $id): void
    {
        $this->forgetCache([
            $this->getCacheKeyForModel($id),
            $this->getCacheKeyForList()
        ]);
    }

    /**
     * Проверить, может ли текущий пользователь обновить модель
     *
     * @param Model $model
     * @return bool
     */
    protected function canUpdate(Model $model): bool
    {
        return true;
    }

    /**
     * Проверить, может ли текущий пользователь удалить модель
     *
     * @param Model $model
     * @return bool
     */
    protected function canDelete(Model $model): bool
    {
        return true;
    }

    /**
     * Получить класс события для создания
     *
     * @return string|null
     */
    protected function getCreateEventClass(): ?string
    {
        return $this->createEventClass;
    }

    /**
     * Получить класс события для обновления
     *
     * @return string|null
     */
    protected function getUpdateEventClass(): ?string
    {
        return $this->updateEventClass;
    }

    /**
     * Получить класс события для удаления
     *
     * @return string|null
     */
    protected function getDeleteEventClass(): ?string
    {
        return $this->deleteEventClass;
    }

    /**
     * Получить ключ кеша для модели по ID
     *
     * @param int $id
     * @return string
     */
    protected function getCacheKeyForModel(int $id): string
    {
        return $this->buildCacheKey('model', $id);
    }

    /**
     * Получить ключ кеша для списка моделей
     *
     * @param array $filters
     * @return string
     */
    protected function getCacheKeyForList(array $filters = []): string
    {
        return $this->buildCacheKey('list', $filters);
    }
}
