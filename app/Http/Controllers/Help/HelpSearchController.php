<?php

namespace App\Http\Controllers\Help;

use App\Http\Controllers\Controller;
use App\Services\Help\HelpSearchService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HelpSearchController extends Controller
{
    protected HelpSearchService $helpSearchService;

    public function __construct(HelpSearchService $helpSearchService)
    {
        $this->helpSearchService = $helpSearchService;
    }

    /**
     * Поиск по базе знаний
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            // Валидация параметров
            if (empty($query)) {
                return $this->errorResponse('Поисковый запрос не может быть пустым', 400);
            }

            $results = $this->helpSearchService->search($query, $page, $perPage);

            Log::info("Результаты поиска помощи возвращены для запроса: {$query}", [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $results->total()
            ]);

            return $this->successResponse(
                $results->items(),
                [
                    'total' => $results->total(),
                    'page' => $results->currentPage(),
                    'per_page' => $results->perPage(),
                    'last_page' => $results->lastPage(),
                ]
            );
        } catch (Exception $e) {
            Log::error('Ошибка при поиске статей помощи: ' . $e->getMessage(), [
                'query' => $request->input('q'),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Ошибка при поиске статей', 500);
        }
    }
}
