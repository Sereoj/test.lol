<?php

namespace App\Strategies\Posts;

class DownloadsSortingStrategy implements SortingStrategy
{
    public function applySorting($query)
    {
        $query->orderByRaw('
            (
                (COALESCE(post_statistics.downloads_count, 0) * 4) +
                (COALESCE(post_statistics.likes_count, 0) * 2) +
                (COALESCE(post_statistics.comments_count, 0) * 2) +
                (COALESCE(post_statistics.downloads_count, 0) /
                    GREATEST(COALESCE(post_statistics.likes_count, 1), 1) * 3) +
                (COALESCE(post_statistics.views_count, 0) * 0.5)
            ) *
            (1 + 1 / (TIMESTAMPDIFF(HOUR, posts.created_at, NOW()) + 1)) DESC
        ');
    }
}
