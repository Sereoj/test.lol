<?php

namespace App\Strategies\Posts;

class DefaultSortingStrategy implements SortingStrategy
{
    public function applySorting($query)
    {
        $query->orderByDesc('relevance_score');
    }
}
