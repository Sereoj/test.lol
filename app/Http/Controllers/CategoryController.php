<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        // Кешируем список категорий
        $cacheKey = 'categories_list';
        if (Cache::has($cacheKey)) {
            // Возвращаем кешированные данные
            return response()->json(Cache::get($cacheKey));
        }

        // Если в кеше нет данных, загружаем из базы данных
        $categories = $this->categoryService->getAllCategory();

        // Кешируем результат на 60 минут
        Cache::put($cacheKey, $categories, now()->addMinutes(60));

        return response()->json($categories);
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $category = $this->categoryService->createCategory($data);

        // Очистка кеша после добавления новой категории
        Cache::forget('categories_list');

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $cacheKey = 'category_' . $id;
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $category = $this->categoryService->getCategoryById($id)->id;

        if ($category) {
            Cache::put($cacheKey, $category, now()->addMinutes(60));

            return response()->json($category);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }

    public function update(CategoryRequest $request, $id)
    {
        $data = $request->validated();
        $category = $this->categoryService->updateCategory($id, $data);

        if ($category) {
            // Очистка кеша после обновления категории
            Cache::forget('category_' . $id);
            Cache::forget('categories_list');

            return response()->json($category);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }

    public function destroy($id)
    {
        $result = $this->categoryService->deleteCategory($id);

        if ($result) {
            // Очистка кеша после удаления категории
            Cache::forget('category_' . $id);
            Cache::forget('categories_list');

            return response()->json(['message' => 'Category deleted successfully']);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }
}
