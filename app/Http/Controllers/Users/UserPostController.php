<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Media\ThumbMediaResource;
use App\Http\Resources\ThumbUserMediaResource;
use App\Models\Users\User;
use App\Services\Posts\PostService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

// Контроллер для работы с постами пользователей
class UserPostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    // Получение списка постов пользователя   
    
    /**
     * @OA\Get(
     *     path="/api/v1/user/{user}/posts",
     *     tags={"Users"},
     *     summary="Get all user posts",
     *     description="Get all user posts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User",
     *         @OA\Schema(type="string")
     *     ),
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
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             )
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
public function index(Request $request, User $user)
    {
        $posts = ThumbUserMediaResource::collection($this->postService->getPostsByUser($user, $request->all()));

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $total = $posts->total();
        $lastPage = ceil($total / $perPage);

        return $this->successResponse($posts, [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage
        ]);
    }
}
