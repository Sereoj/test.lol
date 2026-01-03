<?php

namespace App\Strategies\Posts;

class OldestSortingStrategy implements SortingStrategy
{
    public function applySorting($query)
    {
        $query->orderBy('posts.created_at');
    }
}
