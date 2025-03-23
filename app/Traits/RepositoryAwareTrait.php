<?php

namespace App\Traits;

use App\Repositories\RepositoryInterface;
use App\Services\RepositoryBasedService;

/**
 * Трейт для работы с репозиториями
 */
trait RepositoryAwareTrait
{
    /**
     * Репозиторий
     *
     * @var RepositoryInterface|null
     */
    protected ?RepositoryInterface $repository = null;

    /**
     * Получить репозиторий
     *
     * @return RepositoryInterface|null
     */
    public function getRepository(): ?RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Проверить, инициализирован ли репозиторий
     *
     * @return bool
     */
    public function hasRepository(): bool
    {
        return $this->repository !== null;
    }

    /**
     * Установить репозиторий
     *
     * @param RepositoryInterface $repository
     * @return self
     */
    public function setRepository(RepositoryInterface $repository): self
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Временно заменить репозиторий другим и выполнить функцию
     *
     * @param RepositoryInterface $repository Временный репозиторий
     * @param callable $callback Функция для выполнения
     * @return mixed Результат выполнения функции
     * @throws \RuntimeException Если оригинальный репозиторий не установлен
     */
    public function withRepository(RepositoryInterface $repository, callable $callback): mixed
    {
        if (!$this->hasRepository()) {
            throw new \RuntimeException('Оригинальный репозиторий не установлен. Невозможно временно заменить.');
        }

        $originalRepository = $this->repository;
        $this->repository = $repository;

        try {
            return $callback();
        } finally {
            $this->repository = $originalRepository;
        }
    }

    /**
     * Получить имя класса репозитория
     *
     * @return string|null
     */
    protected function getRepositoryClass(): ?string
    {
        return $this->hasRepository() ? get_class($this->repository) : null;
    }

    /**
     * Получить имя модели, с которой работает репозиторий
     *
     * @return string|null
     */
    protected function getRepositoryModelClass(): ?string
    {
        if (!$this->hasRepository()) {
            return null;
        }
        
        if (method_exists($this->repository, 'getModelClass')) {
            return $this->repository->getModelClass();
        }

        if (method_exists($this->repository, 'model')) {
            return $this->repository->model();
        }

        return null;
    }

    /**
     * Добавить критерий поиска к репозиторию
     *
     * @param mixed $criteria
     * @return self
     * @throws \RuntimeException Если репозиторий не инициализирован
     */
    public function withCriteria(mixed $criteria): self
    {
        if (!$this->hasRepository()) {
            throw new \RuntimeException('Репозиторий не инициализирован. Невозможно добавить критерий.');
        }

        $this->repository->pushCriteria($criteria);
        return $this;
    }

    /**
     * Выполнить операцию без применения критериев поиска
     *
     * @param callable $callback
     * @return mixed
     * @throws \RuntimeException Если репозиторий не инициализирован
     */
    public function withoutCriteria(callable $callback): mixed
    {
        if (!$this->hasRepository()) {
            throw new \RuntimeException('Репозиторий не инициализирован. Невозможно выполнить операцию без критериев.');
        }

        return $this->repository->withoutCriteria($callback);
    }

    /**
     * Безопасно вызвать метод репозитория
     *
     * @param string $method Имя метода
     * @param array $arguments Аргументы метода
     * @param mixed $default Значение по умолчанию, если репозиторий не инициализирован
     * @return mixed
     */
    protected function callRepository(string $method, array $arguments = [], mixed $default = null): mixed
    {
        if (!$this->hasRepository()) {
            return $default;
        }

        if (!method_exists($this->repository, $method)) {
            return $default;
        }

        return $this->repository->{$method}(...$arguments);
    }

    /**
     * Получить класс события для создания
     *
     * @return string|null
     */
    protected function getCreateEventClass(): ?string
    {
        if (property_exists($this, 'createEventClass')) {
            return $this->createEventClass;
        }

        return null;
    }

    /**
     * Получить класс события для обновления
     *
     * @return string|null
     */
    protected function getUpdateEventClass(): ?string
    {
        if (property_exists($this, 'updateEventClass')) {
            return $this->updateEventClass;
        }

        return null;
    }

    /**
     * Получить класс события для удаления
     *
     * @return string|null
     */
    protected function getDeleteEventClass(): ?string
    {
        if (property_exists($this, 'deleteEventClass')) {
            return $this->deleteEventClass;
        }

        return null;
    }
}
