<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Media\ThumbMediaResource;
use App\Http\Resources\Posts\PostResource;
use App\Http\Resources\PostStatResource;
use App\Http\Resources\StorePostResource;
use App\Services\Posts\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

/**
 * @group Посты
 *
 * API для управления постами (публикациями) пользователей
 */
class PostController extends Controller
{
    protected PostService $postService;
    private const CACHE_KEY_POST = 'post_';

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    // Получение списка всех постов   
    /**
     * @OA\Get(
     *     path="/api/v1/posts",
     *     tags={"Posts"},
     *     summary="Get all posts",
     *     description="Get all posts",
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
     *                 @OA\Items(ref="#/components/schemas/ThumbMediaResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function index(Request $request)
    {
        try {
            $userId = Auth::check() ? Auth::id() : null;
            $posts = $this->postService->getPosts($request->all(), $userId);

            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $total = $posts->total();
            $lastPage = ceil($total / $perPage);

            return $this->successResponse(ThumbMediaResource::collection($posts), [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage
            ]);
        } catch (\Exception $exception) {

            return $this->errorResponse($exception->getMessage());
        }
    }

    // Создание нового поста   
    /**
     * @OA\Post(
     *     path="/api/v1/posts",
     *     tags={"Posts"},
     *     summary="Create new post",
     *     description="Create new post",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StorePostRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/StorePostResource")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function store(StorePostRequest $request)
    {
        try {
            $post = $this->postService->createPost($request->validated());

            return $this->successResponse(new StorePostResource($post), [], 201);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    // Получение конкретного поста   
    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Get post by ID",
     *     description="Get post by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function show($id)
    {
        $post = $this->postService->getPost($id);
        return $this->successResponse(new PostResource($post));
    }

    // Обновление поста   
    /**
     * @OA\Put(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Update post",
     *     description="Update post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePostRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function update(UpdatePostRequest $request, int $id)
    {
        $post = $this->postService->updatePost($id, $request->validated());
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return $this->successResponse(
            new PostResource($post),
        );
    }

    // Удаление поста   
    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Delete post",
     *     description="Delete post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
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

    public function destroy(int $id)
    {
        $this->postService->deletePost($id);
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return response()->json([
            'success' => true,
            'message' => 'Пост успешно удален'
        ]);
    }

    // Добавление лайка или дизлайка к посту   
    /**
     * @OA\Post(
     *     path="/api/v1/posts/{id}/like",
     *     tags={"Posts"},
     *     summary="ToggleLike post",
     *     description="ToggleLike post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="action", type="string", enum={"like", "unlike"}, example="like")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostStatResource")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function toggleLike(Request $request, $postId)
    {
        try {
            $userId = Auth::guard('api')->id();
            $action = $request->input('action', 'like');

            $post = match ($action) {
                'unlike' => $this->postService->unlikePost($userId, $postId),
                default => $this->postService->likePost($userId, $postId),
            };
            return $this->successResponse(new PostStatResource($post));
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    // Репост поста   
    /**
     * @OA\Post(
     *     path="/api/v1/posts/{id}/repost",
     *     tags={"Posts"},
     *     summary="Repost post",
     *     description="Repost post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/PostResource")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function repost(int $id)
    {
        $post = $this->postService->repostPost($id);
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return response()->json([
            'success' => true,
            'data' => new PostResource($post),
            'message' => 'Репост успешно создан'
        ]);
    }

    // Скачивание медиа-файлов   
    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}/download",
     *     tags={"Posts"},
     *     summary="Download post",
     *     description="Download post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function download(Request $request, int $id)
    {
        $fileResponse = $this->postService->download($id, $request->input('media'));

        if (!$fileResponse) {
            return response()->json([
                'success' => false,
                'message' => 'Нет доступных медиа'
            ], 404);
        }

        return $fileResponse;
    }
}
