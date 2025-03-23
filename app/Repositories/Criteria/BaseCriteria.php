<?php

namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

/**
 * Базовый класс для критериев поиска
 */
abstract class BaseCriteria implements CriteriaInterface
{
    /**
     * Применить критерий к запросу
     *
     * @param Builder $query
     * @return Builder
     */
    abstract public function apply(Builder $query): Builder;
    
    /**
     * Добавить условие WHERE к запросу
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @param string $boolean
     * @return Builder
     */
    protected function addWhereCondition(Builder $query, string $column, $value, string $operator = '=', string $boolean = 'and'): Builder
    {
        if ($value !== null) {
            return $query->where($column, $operator, $value, $boolean);
        }
        
        return $query;
    }
    
    /**
     * Добавить условие WHERE IN к запросу
     *
     * @param Builder $query
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @param bool $not
     * @return Builder
     */
    protected function addWhereInCondition(Builder $query, string $column, array $values, string $boolean = 'and', bool $not = false): Builder
    {
        if (!empty($values)) {
            return $not
                ? $query->whereNotIn($column, $values, $boolean)
                : $query->whereIn($column, $values, $boolean);
        }
        
        return $query;
    }
    
    /**
     * Добавить условие WHERE BETWEEN к запросу
     *
     * @param Builder $query
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @param bool $not
     * @return Builder
     */
    protected function addWhereBetweenCondition(Builder $query, string $column, array $values, string $boolean = 'and', bool $not = false): Builder
    {
        if (count($values) === 2 && $values[0] !== null && $values[1] !== null) {
            return $not
                ? $query->whereNotBetween($column, $values, $boolean)
                : $query->whereBetween($column, $values, $boolean);
        }
        
        return $query;
    }
    
    /**
     * Добавить условие WHERE NULL к запросу
     *
     * @param Builder $query
     * @param string $column
     * @param string $boolean
     * @param bool $not
     * @return Builder
     */
    protected function addWhereNullCondition(Builder $query, string $column, string $boolean = 'and', bool $not = false): Builder
    {
        return $not
            ? $query->whereNotNull($column, $boolean)
            : $query->whereNull($column, $boolean);
    }
    
    /**
     * Добавить условие WHERE с подзапросом
     *
     * @param Builder $query
     * @param string $column
     * @param \Closure $callback
     * @param string $operator
     * @param string $boolean
     * @return Builder
     */
    protected function addWhereSubQuery(Builder $query, string $column, \Closure $callback, string $operator = '=', string $boolean = 'and'): Builder
    {
        return $query->where($column, $operator, function ($subQuery) use ($callback) {
            $callback($subQuery);
        }, $boolean);
    }
} 