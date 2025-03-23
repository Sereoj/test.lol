<?php

namespace App\Services;

use App\Interfaces\ServiceInterface;
use App\Traits\CacheableTrait;
use App\Traits\ContextAwareTrait;
use App\Traits\EventDispatcherTrait;
use App\Traits\LoggableTrait;
use App\Traits\PipelineTrait;
use App\Traits\TransactionTrait;
use App\Traits\ValidationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Базовый класс для всех сервисов, содержащий общую логику
 */
abstract class BaseService implements ServiceInterface
{
    use CacheableTrait,
        LoggableTrait,
        TransactionTrait,
        ValidationTrait,
        EventDispatcherTrait,
        ContextAwareTrait,
        PipelineTrait;

    /**
     * Модель, с которой работает сервис
     *
     * @var Model|null
     */
    protected ?Model $model = null;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Инициализация сервиса
     *
     * @return void
     */
    protected function initialize(): void
    {
        $serviceName = $this->getServiceName();

        $this->setCachePrefix($serviceName);
        $this->setLogPrefix($serviceName);
    }

    /**
     * Получить название сервиса
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return strtolower(class_basename($this));
    }

    /**
     * Установить префикс для логирования
     *
     * @param string $prefix
     * @return self
     */
    public function setLogPrefix(string $prefix): self
    {
        $this->logPrefix = $prefix;
        return $this;
    }

    /**
     * Установить префикс для кеширования
     *
     * @param string $prefix
     * @return self
     */
    public function setCachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;
        return $this;
    }

    /**
     * Получить название модели
     *
     * @return string Название модели
     */
    protected function getModelName(): string
    {
        $modelClass = $this->getModelClass();

        if (!$modelClass) {
            return $this->getServiceName();
        }

        return strtolower(class_basename($modelClass));
    }

    /**
     * Получить класс модели
     *
     * @return string|null
     */
    protected function getModelClass(): ?string
    {
        return $this->model ? get_class($this->model) : null;
    }

    /**
     * Выполнить операцию в транзакции
     *
     * @param callable $callback
     * @return mixed
     * @throws Exception
     */
    protected function transaction(callable $callback): mixed
    {
        try {
            DB::beginTransaction();
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            $this->logError('Transaction failed', [
                'exception' => $e->getMessage(),
            ], $e);
            throw $e;
        }
    }

    /**
     * Создать ключ кеша с префиксом
     *
     * @param string $key
     * @param mixed ...$params
     * @return string
     */
    protected function buildCacheKey(string $key, ...$params): string
    {
        $prefix = $this->cachePrefix ?: $this->getServiceName();

        return $prefix . ':' . $key . (!empty($params) ? ':' . $this->serializeParams($params) : '');
    }

    /**
     * Сериализовать параметры для ключа кеша
     *
     * @param array $params
     * @return string
     */
    protected function serializeParams(array $params): string
    {
        return implode(':', array_map(function ($param) {
            if (is_array($param)) {
                return md5(serialize($param));
            }
            return (string) $param;
        }, $params));
    }

    /**
     * Получить ключ кеша для модели
     *
     * @param int $id
     * @return string
     */
    protected function getCacheKeyForModel(int $id): string
    {
        return $this->buildCacheKey('model', $id);
    }

    /**
     * Получить ключ кеша для списка
     *
     * @param array $params
     * @return string
     */
    protected function getCacheKeyForList(array $params = []): string
    {
        return $this->buildCacheKey('list', $params);
    }

    /**
     * Безопасное выполнение функции с логированием ошибок
     *
     * @param callable $callback
     * @param array $context
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function safeExecute(callable $callback, array $context = [], mixed $defaultValue = null): mixed
    {
        try {
            return $callback();
        } catch (Exception $e) {
            $this->logError('Ошибка при выполнении операции', $context, $e);
            return $defaultValue;
        }
    }

    /**
     * Маскировать конфиденциальные данные для логирования
     *
     * @param array $data
     * @return array
     */
    protected function maskSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***MASKED***';
            }
        }

        return $data;
    }

    /**
     * Создать новую запись
     *
     * @param array $data
     * @return Model|null
     */
    abstract public function create(array $data): ?Model;

    /**
     * Обновить запись
     *
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    abstract public function update(int $id, array $data): ?Model;

    /**
     * Удалить запись
     *
     * @param int $id
     * @return bool
     */
    abstract public function delete(int $id): bool;

    /**
     * Найти запись по ID
     *
     * @param int $id
     * @return Model|null
     */
    abstract public function findById(int $id): ?Model;

    /**
     * Получить все записи
     *
     * @param array $relations Связи для загрузки
     * @return Collection
     */
    public function getAll(array $relations = [])
    {
        $cacheKey = $this->getCacheKeyForList($relations);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($relations) {
            $result = $this->getAllModels($relations);

            $this->logInfo("Получен список записей {$this->getModelName()}", [
                'count' => count($result),
                'relations' => $relations
            ]);

            return $result;
        });
    }

    /**
     * Получить все модели
     *
     * @param array $relations
     * @return mixed
     */
    abstract protected function getAllModels(array $relations = []): mixed;

    /**
     * Очистить кеш для модели
     *
     * @param int $id ID модели
     * @return void
     */
    protected function clearCacheForModel(int $id): void
    {
        $this->forgetCache($this->getCacheKeyForModel($id));
        // Также сбрасываем кеш списков, поскольку модель могла изменить их
        $this->clearServiceCache($id);
    }

    /**
     * Очистить кеш сервиса
     *
     * @param int $id
     * @return void
     */
    protected function clearServiceCache(int $id): void
    {
        // Дополнительная логика очистки кеша
    }

    /**
     * Получить класс события для создания
     *
     * @return string|null
     */
    protected function getCreateEventClass(): ?string
    {
        return null;
    }

    /**
     * Получить класс события для обновления
     *
     * @return string|null
     */
    protected function getUpdateEventClass(): ?string
    {
        return null;
    }

    /**
     * Получить класс события для удаления
     *
     * @return string|null
     */
    protected function getDeleteEventClass(): ?string
    {
        return null;
    }

    /**
     * Хук после создания модели
     *
     * @param Model $model
     * @param array $data
     * @return void
     */
    protected function afterCreate(Model $model, array $data): void
    {
        // Реализация в наследниках
    }

    /**
     * Хук после обновления модели
     *
     * @param Model $model
     * @param array $data
     * @return void
     */
    protected function afterUpdate(Model $model, array $data): void
    {
        // Реализация в наследниках
    }

    /**
     * Хук перед удалением модели
     *
     * @param Model $model
     * @return void
     */
    protected function beforeDelete(Model $model): void
    {
        // Реализация в наследниках
    }

    /**
     * Хук после удаления модели
     *
     * @param Model $model
     * @return void
     */
    protected function afterDelete(Model $model): void
    {
        // Реализация в наследниках
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
     * Поиск записей по условиям
     *
     * @param array $conditions
     * @param array $relations
     * @return Collection
     */
    public function findWhere(array $conditions, array $relations = []): Collection
    {
        $cacheKey = $this->getCacheKeyForConditions($conditions, $relations);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($conditions, $relations) {
            $result = $this->findWhereModels($conditions, $relations);

            $this->logInfo("Найдены записи {$this->getModelName()} по условиям", [
                'conditions' => $conditions,
                'relations' => $relations,
                'count' => count($result)
            ]);

            return $result;
        });
    }

    /**
     * Найти модели по условиям
     *
     * @param array $conditions
     * @param array $relations
     * @return mixed
     */
    abstract protected function findWhereModels(array $conditions, array $relations = []): mixed;

    /**
     * Получить ключ кеша для поиска по условиям
     *
     * @param array $conditions
     * @param array $relations
     * @return string
     */
    protected function getCacheKeyForConditions(array $conditions, array $relations = []): string
    {
        return $this->buildCacheKey('conditions', $conditions, $relations);
    }
}
