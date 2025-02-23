<?php

namespace App\Strategies\Posts;

class PopularitySortingStrategy implements SortingStrategy
{
    public function applySorting($query)
    {
        $query->orderByRaw('
            (
                COALESCE(post_statistics.likes_count, 0) * 2 +
                COALESCE(post_statistics.reposts_count, 0) * 3 +
                COALESCE(post_statistics.comments_count, 0) * 1.5 +
                COALESCE(post_statistics.downloads_count, 0) * 4 +
                COALESCE(post_statistics.purchases_count, 0) * 5 +
                COALESCE(post_statistics.views_count, 0) * 0.5
            ) / POW(TIMESTAMPDIFF(HOUR, posts.created_at, NOW()) + 1, 0.5) DESC
        ');
    }
}
