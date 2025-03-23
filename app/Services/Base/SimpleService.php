<?php

namespace App\Services\Base;

use App\Services\BaseService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Простой сервис без зависимости от репозитория
 */
abstract class SimpleService extends BaseService
{
    /**
     * Продолжительность кеширования по умолчанию (в минутах)
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
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
     * Получить кеш ключ для модели
     *
     * @param int $id
     * @return string
     */
    protected function getCacheKeyForModel(int $id): string
    {
        return $this->buildCacheKey('model', ['id' => $id]);
    }

    /**
     * Получить кеш ключ для списка
     *
     * @param array $filters
     * @return string
     */
    protected function getCacheKeyForList(array $filters = []): string
    {
        return $this->buildCacheKey('list', $filters);
    }

    /**
     * Безопасное получение элемента из кеша или хранилища
     *
     * @param string $key Ключ кеша
     * @param int $minutes Время хранения в минутах
     * @param callable $callback Функция для получения данных
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    protected function getFromCacheOrStore(string $key, int $minutes, callable $callback, $default = null)
    {
        // Проверяем, включено ли кеширование
        if (property_exists($this, 'useCache') && !$this->useCache) {
            try {
                return $callback();
            } catch (Exception $e) {
                $this->logError('Ошибка при получении данных без кеша', [], $e);
                return $default;
            }
        }

        try {
            return Cache::remember($key, $minutes * 60, function () use ($callback) {
                return $callback();
            });
        } catch (Exception $e) {
            $this->logError('Ошибка при получении данных из кеша', [
                'key' => $key,
                'minutes' => $minutes
            ], $e);

            try {
                return $callback();
            } catch (Exception $e2) {
                $this->logError('Повторная ошибка при получении данных после сбоя кеша', [], $e2);
                return $default;
            }
        }
    }

    /**
     * Очистить кеш по ключам
     *
     * @param array $keys
     * @return void
     */
    protected function forgetCache(array $keys): void
    {
        foreach ($keys as $key) {
            try {
                Cache::forget($key);
            } catch (Exception $e) {
                $this->logWarning('Ошибка при очистке кеша', ['key' => $key], $e);
            }
        }
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
     * Получить все модели
     *
     * @param array $relations
     * @return Collection
     */
    abstract protected function getAllModels(array $relations = []): Collection;
}
