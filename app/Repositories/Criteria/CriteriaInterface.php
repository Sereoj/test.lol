<?php

namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Интерфейс для критериев поиска
 */
interface CriteriaInterface
{
    /**
     * Применить критерий к запросу
     *
     * @param Builder $query
     * @return Builder
     */
    public function apply(Builder $query): Builder;
} 