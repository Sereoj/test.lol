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
        $cacheKey = 'posts_'.md5(json_encode($request->all()).'_'.$userId);

        $posts = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($request, $userId) {
            return $this->postService->getPosts($request->all(), $userId);
        });

        return response()->json($posts);
    }

    /**
     * Создать новый пост.
     */
    public function store(StorePostRequest $request)
    {
        $post = $this->postService->createPost($request->validated());
        Cache::forget('posts_'.md5(json_encode($request->all())));

        return response()->json($post, 201);
    }

    /**
     * Получить пост по ID.
     */
    public function show($id)
    {
        $cacheKey = 'post_'.$id;

        $post = Cache::remember($cacheKey, now()->addSeconds(2), function () use ($id) {
            return new PostResource($this->postService->getPost($id));
        });

        return response()->json($post);
    }

    /**
     * Обновить пост.
     */
    public function update(UpdatePostRequest $request, int $id)
    {
        $post = $this->postService->updatePost($id, $request->validated());
        Cache::forget('post_'.$id);

        return response()->json($post);
    }

    /**
     * Удалить пост.
     */
    public function destroy(int $id)
    {
        $this->postService->deletePost($id);
        Cache::forget('post_'.$id);

        return response()->json(null, 204);
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
        Cache::forget('post_'.$id);

        return response()->json($post);
    }

    /**
     * Сделать репост поста.
     */
    public function repost(int $id)
    {
        $post = $this->postService->repostPost($id);
        Cache::forget('post_'.$id);

        return response()->json($post);
    }

    public function download(Request $request, int $id)
    {
        $fileResponse = $this->postService->download($id, $request->input('media'));

        return $fileResponse ?? response()->json(['message' => 'No media found'], 404);
    }
}
