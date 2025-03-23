<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Posts\PostResource;
use App\Services\Posts\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    protected PostService $postService;
    
    private const CACHE_SECONDS = 30;
    private const CACHE_SECONDS_POST = 2;
    private const CACHE_KEY_POSTS = 'posts_';
    private const CACHE_KEY_POST = 'post_';

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Получить все посты.
     */
    public function index(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $cacheKey = self::CACHE_KEY_POSTS . md5(json_encode($request->all()).'_'.$userId);

        $posts = $this->getFromCacheOrStore($cacheKey, self::CACHE_SECONDS / 60, function () use ($request, $userId) {
            $filters = $request->all();
            if ($userId) {
                $filters['user_id'] = $userId;
            }
            return $this->postService->getPosts($filters);
        });

        return $this->successResponse($posts);
    }

    /**
     * Создать новый пост.
     */
    public function store(StorePostRequest $request)
    {
        $post = $this->postService->createPost($request->validated());
        $this->forgetCache(self::CACHE_KEY_POSTS . md5(json_encode($request->all())));

        return $this->successResponse($post, 201);
    }

    /**
     * Получить пост по ID.
     */
    public function show($id)
    {
        $cacheKey = self::CACHE_KEY_POST . $id;

        $post = $this->getFromCacheOrStore($cacheKey, self::CACHE_SECONDS_POST / 60, function () use ($id) {
            return new PostResource($this->postService->getPost((int)$id));
        });

        return $this->successResponse($post);
    }

    /**
     * Обновить пост.
     */
    public function update(UpdatePostRequest $request, int $id)
    {
        $post = $this->postService->updatePost($id, $request->validated());
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return $this->successResponse($post);
    }

    /**
     * Удалить пост.
     */
    public function destroy(int $id)
    {
        $this->postService->deletePost($id);
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return $this->successResponse(null, 204);
    }

    /**
     * Лайкнуть или дизлайкнуть пост.
     */
    public function toggleLike(int $id, Request $request)
    {
        $post = [];
        $action = $request->input('action');

        if ($action === 'like') {
            $post = $this->postService->likePost($id);
        } elseif ($action === 'dislike') {
            $post = $this->postService->unlikePost($id);
        }
        
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return $this->successResponse($post);
    }

    /**
     * Сделать репост поста.
     */
    public function repost(int $id)
    {
        $post = $this->postService->repostPost($id);
        $this->forgetCache(self::CACHE_KEY_POST . $id);

        return $this->successResponse($post);
    }

    /**
     * Скачать медиа файлы поста
     */
    public function download(Request $request, int $id)
    {
        $fileResponse = $this->postService->download($id, $request->input('media'));

        return $fileResponse ?? $this->errorResponse('No media found', 404);
    }
}
