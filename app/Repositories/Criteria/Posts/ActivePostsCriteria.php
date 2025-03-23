<?php

namespace App\Repositories\Criteria\Posts;

use App\Repositories\Criteria\CriteriaInterface;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Критерий для выборки только активных постов
 */
class ActivePostsCriteria implements CriteriaInterface
{
    /**
     * Применить критерий к запросу
     *
     * @param Builder $query
     * @return Builder
     */
    public function apply(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
} 