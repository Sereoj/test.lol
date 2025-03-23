<?php

namespace App\Repositories\Criteria\Users;

use App\Repositories\Criteria\BaseCriteria;
use Illuminate\Database\Eloquent\Builder;

/**
 * Критерий для выборки только активных пользователей
 */
class ActiveUsersCriteria extends BaseCriteria
{
    /**
     * @var bool
     */
    protected bool $isActive;
    
    /**
     * Конструктор
     * 
     * @param bool $isActive Флаг активности пользователя
     */
    public function __construct(bool $isActive = true)
    {
        $this->isActive = $isActive;
    }
    
    /**
     * Применить критерий к запросу
     *
     * @param Builder $query
     * @return Builder
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('is_active', $this->isActive);
    }
} 