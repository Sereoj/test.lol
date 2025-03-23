<?php

namespace App\Services\Posts\Assistants;

use App\Services\Base\SimpleService;
use App\Strategies\Posts\DefaultSortingStrategy;
use App\Strategies\Posts\DownloadsSortingStrategy;
use App\Strategies\Posts\LikesSortingStrategy;
use App\Strategies\Posts\PopularitySortingStrategy;
use Exception;

/**
 * Сервис для сортировки постов
 */
class SortingService extends SimpleService
{
    /**
     * Стратегии сортировки
     *
     * @var array
     */
    protected array $strategies;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('SortingService');
        
        $this->strategies = [
            'popularity' => new PopularitySortingStrategy(),
            'downloads' => new DownloadsSortingStrategy(),
            'likes' => new LikesSortingStrategy(),
            'default' => new DefaultSortingStrategy(),
        ];
    }
    
    /**
     * Применение стратегии сортировки
     *
     * @param mixed $query
     * @param string $strategyName
     * @return void
     */
    public function apply($query, string $strategyName)
    {
        $this->logInfo('Применение стратегии сортировки', [
            'strategy' => $strategyName
        ]);
        
        try {
            if (isset($this->strategies[$strategyName])) {
                $strategy = $this->strategies[$strategyName];
                $this->logInfo('Используется стратегия сортировки', ['strategy' => $strategyName]);
            } else {
                $strategy = $this->strategies['default'];
                $this->logWarning('Стратегия сортировки не найдена, используется по умолчанию', ['requested_strategy' => $strategyName]);
            }
            
            $strategy->applySorting($query);
        } catch (Exception $e) {
            $this->logError('Ошибка при применении стратегии сортировки', [
                'strategy' => $strategyName
            ], $e);
            
            // Применяем сортировку по умолчанию при ошибке
            $this->strategies['default']->applySorting($query);
        }
    }
}
