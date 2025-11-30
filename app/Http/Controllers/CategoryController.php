<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CategoryRequest;
use App\Http\Resources\ShortCategoryResource;
use App\Services\Content\CategoryService;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

// Контроллер для работы с категориями
class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    private const CACHE_KEY_CATEGORIES_LIST = 'categories_list';
    private const CACHE_KEY_CATEGORY = 'category_';
    private const CACHE_MINUTES = 60;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="Get all categories",
     *     description="Get all categories",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Category")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */


    public function index()
    {
        $categories = $this->getFromCacheOrStore(self::CACHE_KEY_CATEGORIES_LIST, self::CACHE_MINUTES, function () {
            return ShortCategoryResource::collection($this->categoryService->getAll());
        });

        return $this->successResponse($categories);
    }    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="Create new category",
     *     description="Create new category",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */


    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $category = $this->categoryService->create($data);

        $this->forgetCache(self::CACHE_KEY_CATEGORIES_LIST);

        return $this->successResponse($category, [], 201);
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
    }    /**
     * @OA\Put(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories"},
     *     summary="Update category",
     *     description="Update category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */


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
    }    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{category}",
     *     tags={"Categories"},
     *     summary="Delete category",
     *     description="Delete category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */


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
