<?php

namespace App\Services\Posts\Assistants;

use App\Strategies\Posts\DefaultSortingStrategy;
use App\Strategies\Posts\DownloadsSortingStrategy;
use App\Strategies\Posts\LikesSortingStrategy;
use App\Strategies\Posts\PopularitySortingStrategy;

class SortingService
{
    /**
     * Применение стратегии сортировки
     */
    public function apply($query, string $strategyName)
    {
        $strategies = [
            'popularity' => new PopularitySortingStrategy(),
            'downloads' => new DownloadsSortingStrategy(),
            'likes' => new LikesSortingStrategy(),
            'default' => new DefaultSortingStrategy(),
        ];

        if (isset($strategies[$strategyName])) {
            $strategies[$strategyName]->applySorting($query);
        } else {
            $strategies['default']->applySorting($query);
        }
    }
}
