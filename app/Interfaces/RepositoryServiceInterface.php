<?php

namespace App\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

interface RepositoryServiceInterface extends ServiceInterface
{
    /**
     * Получить репозиторий
     * 
     * @return RepositoryInterface|null
     */
    public function getRepository(): ?RepositoryInterface;
    
    /**
     * Установить репозиторий
     * 
     * @param RepositoryInterface $repository
     * @return self
     */
    public function setRepository(RepositoryInterface $repository): self;
    
    /**
     * Создать новую запись через репозиторий
     * 
     * @param array $data
     * @return Model|null
     */
    public function create(array $data): ?Model;
    
    /**
     * Обновить запись через репозиторий
     * 
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(int $id, array $data): ?Model;
    
    /**
     * Удалить запись через репозиторий
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
    
    /**
     * Найти запись по ID
     * 
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model;
} 