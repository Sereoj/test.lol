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
    }                            /**
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
     *             @OA\Property(property="message", type="string", example="Resource deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
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
