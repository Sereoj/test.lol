<?php

namespace App\Strategies\Posts;

class NewestSortingStrategy implements SortingStrategy
{
    public function applySorting($query)
    {
        $query->orderByDesc('posts.created_at');
    }
}
