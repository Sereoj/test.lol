<?php

namespace App\Traits;

use Cache;

/**
 * Трейт для работы с кешированием
 */
trait CacheableTrait
{
    /**
     * Префикс для кеша
     *
     * @var string
     */
    protected string $cachePrefix = "";

    /**
     * Количество минут для хранения в кеше по умолчанию
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Флаг использования кеширования
     *
     * @var bool
     */
    protected bool $cacheEnabled = true;

    /**
     * Получить значение из кеша или вычислить и сохранить его
     *
     * @param string $key Ключ кеша
     * @param int|null $minutes Время хранения в минутах (null - вечно)
     * @param callable $callback Функция для вычисления значения
     * @return mixed
     */
    protected function getFromCacheOrStore(string $key, ?int $minutes, callable $callback): mixed
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        if ($minutes === null) {
            return Cache::rememberForever($key, $callback);
        }

        return Cache::remember($key, $minutes, $callback);
    }

    /**
     * Установить значение в кеш
     *
     * @param string $key Ключ кеша
     * @param mixed $value Значение
     * @param int|null $minutes Время хранения в минутах (null - вечно)
     * @return bool
     */
    protected function putInCache(string $key, $value, ?int $minutes = null): bool
    {
        if (!$this->cacheEnabled) {
            return false;
        }

        if ($minutes === null) {
            return Cache::forever($key, $value);
        }

        return Cache::put($key, $value, $minutes);
    }

    /**
     * Проверить наличие значения в кеше
     *
     * @param string $key Ключ кеша
     * @return bool
     */
    protected function hasInCache(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * Получить значение из кеша
     *
     * @param string $key Ключ кеша
     * @param mixed|null $default Значение по умолчанию
     * @return mixed
     */
    protected function getFromCache(string $key, mixed $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    /**
     * Удалить значение из кеша
     *
     * @param array|string $keys Ключ(и) кеша
     * @return bool
     */
    protected function forgetCache(array|string $keys): bool
    {
        if (is_array($keys)) {
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            return true;
        }

        return Cache::forget($keys);
    }

    /**
     * Очистить кеш с заданным тегом
     *
     * @param string|array $tags Тег(и) для очистки
     * @return bool
     */
    protected function flushCacheByTags($tags): bool
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            return Cache::tags($tags)->flush();
        }

        // Если теги не поддерживаются, логируем предупреждение
        if (method_exists($this, 'logWarning')) {
            $this->logWarning('Cache tags are not supported with the current cache driver');
        }

        return false;
    }

    /**
     * Включить кеширование
     *
     * @return self
     */
    public function enableCache(): self
    {
        $this->cacheEnabled = true;
        return $this;
    }

    /**
     * Отключить кеширование
     *
     */
    public function disableCache(): self
    {
        $this->cacheEnabled = false;
        return $this;
    }

    /**
     * Проверить, включено ли кеширование
     *
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    /**
     * Выполнить операцию с отключенным кешированием
     *
     * @param callable $callback Операция для выполнения
     * @return mixed
     */
    public function withoutCache(callable $callback)
    {
        $originalState = $this->cacheEnabled;
        $this->cacheEnabled = false;

        try {
            $result = $callback();
            return $result;
        } finally {
            $this->cacheEnabled = $originalState;
        }
    }

    /**
     * Построить ключ кеша из префикса и параметров
     *
     * @param string $type Тип операции (model, list, etc)
     * @param mixed ...$params Параметры для построения ключа
     * @return string
     */
    protected function buildCacheKey(string $type, ...$params): string
    {
        $key = $this->cachePrefix ?: get_class($this);
        $key = strtolower(str_replace('\\', '.', $key)) . '.' . $type;

        if (!empty($params)) {
            foreach ($params as $param) {
                if (is_numeric($param) || is_string($param)) {
                    $key .= '.' . $param;
                } elseif (is_array($param) && !empty($param)) {
                    $key .= '.' . md5(serialize($param));
                }
            }
        }

        return $key;
    }

    /**
     * Установить префикс кеша
     *
     * @param string $prefix
     */
    public function setCachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;
        return $this;
    }

    /**
     * Получить префикс кеша
     *
     * @return string|null
     */
    public function getCachePrefix(): ?string
    {
        return $this->cachePrefix;
    }

    /**
     * Установить время хранения в кеше по умолчанию
     *
     * @param int $minutes
     * @return BaseService|Controller|SimpleService|CacheableTrait
     */
    public function setDefaultCacheMinutes(int $minutes): self
    {
        $this->defaultCacheMinutes = $minutes;
        return $this;
    }

    /**
     * Получить ключ кеша для тегов
     *
     * @param mixed ...$params
     * @return array
     */
    protected function getCacheTags(...$params): array
    {
        $baseTag = $this->cachePrefix ?: get_class($this);
        $baseTag = strtolower(str_replace('\\', '.', $baseTag));

        $tags = [$baseTag];

        foreach ($params as $param) {
            if (is_string($param) || is_numeric($param)) {
                $tags[] = $baseTag . '.' . $param;
            }
        }

        return $tags;
    }

    /**
     * Кешировать результат метода
     *
     * @param string $method Имя метода
     * @param array $params Параметры метода
     * @param int|null $minutes Время кеширования
     * @return mixed
     */
    protected function cacheMethodResult(string $method, array $params = [], ?int $minutes = null): mixed
    {
        $cacheKey = $this->buildCacheKey($method, $params);
        $minutes = $minutes ?? $this->defaultCacheMinutes;

        return $this->getFromCacheOrStore($cacheKey, $minutes, function () use ($method, $params) {
            return call_user_func_array([$this, $method], $params);
        });
    }
}
