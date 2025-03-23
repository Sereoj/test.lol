<?php

namespace App\Services\Posts\Assistants;

use App\Services\Base\SimpleService;
use Exception;

/**
 * Сервис для фильтрации и сортировки постов
 */
class PostFilteringService extends SimpleService
{
    /**
     * Сервис сортировки
     *
     * @var SortingService
     */
    private SortingService $sortingService;
    
    /**
     * Сервис фильтрации по временному диапазону
     *
     * @var TimeFrameFilterService
     */
    private TimeFrameFilterService $timeFrameFilter;
    
    /**
     * Сервис фильтрации по типу медиа
     *
     * @var MediaTypeFilterService
     */
    private MediaTypeFilterService $mediaTypeFilter;

    /**
     * Конструктор
     *
     * @param SortingService $sortingService
     * @param TimeFrameFilterService $timeFrameFilter
     * @param MediaTypeFilterService $mediaTypeFilter
     */
    public function __construct(
        SortingService $sortingService, 
        TimeFrameFilterService $timeFrameFilter,
        MediaTypeFilterService $mediaTypeFilter
    ) {
        parent::__construct();
        $this->sortingService = $sortingService;
        $this->timeFrameFilter = $timeFrameFilter;
        $this->mediaTypeFilter = $mediaTypeFilter;
        $this->setLogPrefix('PostFilteringService');
    }

    /**
     * Применение фильтров и сортировки к запросу
     *
     * @param mixed $query
     * @param array $filters
     * @param string|null $sortStrategy
     * @return void
     */
    public function applyFiltersAndSorting($query, array $filters, ?string $sortStrategy = 'default')
    {
        $this->logInfo('Применение фильтров и сортировки к запросу', [
            'filters' => $filters,
            'sort_strategy' => $sortStrategy
        ]);
        
        try {
            // Применение фильтрации по временному диапазону
            $this->timeFrameFilter->apply($query, $filters['time_frame'] ?? null);

            // Применение фильтрации по типу медиа
            $this->mediaTypeFilter->apply(
                $query, 
                $filters['media_types'] ?? null, 
                $filters['media_filter_mode'] ?? 'or'
            );

            // Применение сортировки
            $this->sortingService->apply($query, $sortStrategy ?? 'default');
            
            $this->logInfo('Фильтры и сортировка успешно применены');
        } catch (Exception $e) {
            $this->logError('Ошибка при применении фильтров и сортировки', [
                'filters' => $filters,
                'sort_strategy' => $sortStrategy
            ], $e);
        }
    }
}
