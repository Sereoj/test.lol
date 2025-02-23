<?php

namespace App\Services\Posts\Assistants;

class TimeFrameFilterService
{
    /**
     * Применение фильтрации по временному диапазону
     */
    public function apply($query, ?string $timeFrame)
    {
        $timeFrameMap = [
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
        ];

        if ($timeFrame && isset($timeFrameMap[$timeFrame])) {
            $query->where('posts.created_at', '>=', $timeFrameMap[$timeFrame]);
        }
    }
}
