<?php

namespace App\Http\Controllers;

use App\Services\PostSearchService;
use App\Services\SearchSuggestionService;
use Illuminate\Http\Request;

class PostSearchController extends Controller
{
    protected PostSearchService $searchService;

    private SearchSuggestionService $searchSuggestionService;

    public function __construct(PostSearchService $searchService, SearchSuggestionService $searchSuggestionService)
    {
        $this->searchService = $searchService;
        $this->searchSuggestionService = $searchSuggestionService;
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        // Валидация запроса
        $validated = $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $results = $this->searchService->search($query);

        return response()->json($results);
    }

    public function suggest(Request $request)
    {
        $query = $request->input('query');
        $suggestions = $this->searchSuggestionService->suggest($query);

        return response()->json($suggestions);
    }
}
