<?php

namespace App\Traits;

use App\Repositories\Criteria\CriteriaInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Трейт для работы с критериями поиска в репозиториях
 */
trait CriteriaAwareTrait
{
    /**
     * @var Collection
     */
    protected Collection $criteria;
    
    /**
     * @var bool
     */
    protected bool $criteriaEnabled = true;
    
    /**
     * Инициализация коллекции критериев
     */
    public function bootCriteriaAwareTrait()
    {
        $this->criteria = collect([]);
    }
    
    /**
     * Добавить критерий
     *
     * @param CriteriaInterface $criteria
     * @return self
     */
    public function pushCriteria(CriteriaInterface $criteria): self
    {
        $this->criteria->push($criteria);
        return $this;
    }
    
    /**
     * Добавить несколько критериев
     *
     * @param array $criteria
     * @return self
     */
    public function pushCriterias(array $criteria): self
    {
        foreach ($criteria as $criterion) {
            $this->pushCriteria($criterion);
        }
        
        return $this;
    }
    
    /**
     * Получить коллекцию критериев
     *
     * @return Collection
     */
    public function getCriteria(): Collection
    {
        return $this->criteria;
    }
    
    /**
     * Получить критерий по классу
     *
     * @param string $class
     * @return CriteriaInterface|null
     */
    public function getCriteriaByClass(string $class)
    {
        return $this->criteria->first(function ($criterion) use ($class) {
            return $criterion instanceof $class;
        });
    }
    
    /**
     * Удалить критерий
     *
     * @param CriteriaInterface $criteria
     * @return self
     */
    public function removeCriteria(CriteriaInterface $criteria): self
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($criteria) {
            return get_class($item) === get_class($criteria);
        });
        
        return $this;
    }
    
    /**
     * Удалить критерий по классу
     *
     * @param string $class
     * @return self
     */
    public function removeCriteriaByClass(string $class): self
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($class) {
            return $item instanceof $class;
        });
        
        return $this;
    }
    
    /**
     * Очистить все критерии
     *
     * @return self
     */
    public function clearCriteria(): self
    {
        $this->criteria = collect([]);
        return $this;
    }
    
    /**
     * Отключить применение критериев
     *
     * @return self
     */
    public function disableCriteria(): self
    {
        $this->criteriaEnabled = false;
        return $this;
    }
    
    /**
     * Включить применение критериев
     *
     * @return self
     */
    public function enableCriteria(): self
    {
        $this->criteriaEnabled = true;
        return $this;
    }
    
    /**
     * Проверить, включено ли применение критериев
     *
     * @return bool
     */
    public function isCriteriaEnabled(): bool
    {
        return $this->criteriaEnabled;
    }
    
    /**
     * Применить критерии к запросу
     *
     * @param Builder $query
     * @return Builder
     */
    protected function applyCriteria(Builder $query): Builder
    {
        if (!$this->criteriaEnabled) {
            return $query;
        }
        
        // Применяем каждый критерий к запросу
        $this->criteria->each(function ($criteria) use (&$query) {
            $query = $criteria->apply($query);
        });
        
        return $query;
    }
    
    /**
     * Временно отключить критерии и выполнить запрос
     *
     * @param callable $callback
     * @return mixed
     */
    public function withoutCriteria(callable $callback)
    {
        $originalState = $this->criteriaEnabled;
        $this->criteriaEnabled = false;
        
        try {
            return $callback();
        } finally {
            $this->criteriaEnabled = $originalState;
        }
    }
} 