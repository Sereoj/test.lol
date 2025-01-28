<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CategoryRequest;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $artworks = $this->categoryService->getAllCategory();

        return response()->json($artworks);
    }

    public function store(CategoryRequest $request)
    {
        $artwork = $this->categoryService->createCategory($request->validated());

        return response()->json($artwork, 201);
    }

    public function show($id)
    {
        $artwork = $this->categoryService->getCategoryById($id);

        return response()->json($artwork);
    }

    public function update(CategoryRequest $request, $id)
    {
        $artwork = $this->categoryService->updateCategory($id, $request->validated());

        return response()->json($artwork);
    }

    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id);

        return response()->json(null, 204);
    }
}
