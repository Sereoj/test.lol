<?php

namespace App\Services\Help;

use App\Models\Help\HelpArticle;
use Illuminate\Pagination\LengthAwarePaginator;

class HelpSearchService
{
    /**
     * Поиск по статьям помощи
     *
     * @param string $query Поисковый запрос
     * @param int $page Номер страницы
     * @param int $perPage Количество результатов на странице
     * @return LengthAwarePaginator
     */
    public function search(string $query, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return HelpArticle::query()
            ->published()
            ->search($query)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
