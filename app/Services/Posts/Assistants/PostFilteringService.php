<?php

namespace App\Services\Posts\Assistants;

class PostFilteringService
{
    private SortingService $sortingService;
    private TimeFrameFilterService $timeFrameFilter;

    public function __construct(SortingService $sortingService, TimeFrameFilterService $timeFrameFilter)
    {
        $this->sortingService = $sortingService;
        $this->timeFrameFilter = $timeFrameFilter;
    }

    /**
     * Применение фильтров и сортировки к запросу
     */
    public function applyFiltersAndSorting($query, array $filters, ?string $userPreferences)
    {
        $this->timeFrameFilter->apply($query, $filters['time_frame'] ?? null);
        $this->sortingService->apply($query, $userPreferences ?? 'default');
    }
}
