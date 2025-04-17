<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CategoryRequest;
use App\Http\Resources\ShortCategoryResource;
use App\Services\Content\CategoryService;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    private const CACHE_KEY_CATEGORIES_LIST = 'categories_list';
    private const CACHE_KEY_CATEGORY = 'category_';
    private const CACHE_MINUTES = 60;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->getFromCacheOrStore(self::CACHE_KEY_CATEGORIES_LIST, self::CACHE_MINUTES, function () {
            return ShortCategoryResource::collection($this->categoryService->getAll());
        });

        return $this->successResponse($categories);
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $category = $this->categoryService->create($data);

        $this->forgetCache(self::CACHE_KEY_CATEGORIES_LIST);

        return $this->successResponse($category, 201);
    }

    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_CATEGORY . $id;

        $category = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
            return $this->categoryService->getById($id)->id;
        });

        if ($category) {
            return $this->successResponse($category);
        }

        return $this->errorResponse('Category not found', 404);
    }

    public function update(CategoryRequest $request, $id)
    {
        $data = $request->validated();
        $category = $this->categoryService->update($id, $data);

        if ($category) {
            $this->forgetCache([
                self::CACHE_KEY_CATEGORY . $id,
                self::CACHE_KEY_CATEGORIES_LIST
            ]);

            return $this->successResponse($category);
        }

        return $this->errorResponse('Category not found', 404);
    }

    public function destroy($id)
    {
        $result = $this->categoryService->delete($id);

        if ($result) {
            $this->forgetCache([
                self::CACHE_KEY_CATEGORY . $id,
                self::CACHE_KEY_CATEGORIES_LIST
            ]);

            return $this->successResponse(['message' => 'Category deleted successfully']);
        }

        return $this->errorResponse('Category not found', 404);
    }
}
