<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Media\ThumbMediaResource;
use App\Http\Resources\Posts\PostResource;
use App\Http\Resources\PostStatResource;
use App\Services\Posts\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
        }catch (\Exception $exception){

            return $this->errorResponse($exception->getMessage());
        }
    }

    // Создание нового поста
    public function store(StorePostRequest $request)
    {
        try {
            $post = $this->postService->createPost($request->validated());

            return $this->successResponse(new PostResource($post), [], 201);
        } catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage());
        }
    }

    // Получение конкретного поста
    public function show($id)
    {
        $post = $this->postService->getPost($id);
        return $this->successResponse(new PostResource($post));
    }

    // Обновление поста
    public function update(UpdatePostRequest $request, int $id)
    {
        $post = $this->postService->updatePost($id, $request->validated());
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return $this->successResponse(
            new PostResource($post),
        );
    }

    // Удаление поста
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
        } catch (\Exception $exception)
        {
            return $this->errorResponse($exception->getMessage());
        }
    }

    // Репост поста
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
