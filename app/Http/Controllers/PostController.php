<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService
    ) {
        $this->postService = $postService;
    }

    public function index(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $posts = $this->postService->getPosts($request->all(), $userId);

        return response()->json($posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = $this->postService->createPost($request->validated());

        return response()->json($post, 201);
    }

    public function show(int $id)
    {
        $post = $this->postService->getPost($id);

        return response()->json($post);
    }

    public function update(UpdatePostRequest $request, int $id)
    {
        $post = $this->postService->updatePost($id, $request->validated());

        return response()->json($post);
    }

    public function destroy(int $id)
    {
        $this->postService->deletePost($id);

        return response()->json(null, 204);
    }

    public function toggleLike(int $id, Request $request)
    {
        $action = $request->input('action');

        if ($action === 'like') {
            $post = $this->postService->likePost($id);
        } elseif ($action === 'dislike') {
            $post = $this->postService->unlikePost($id);
        }

        return response()->json($post);
    }

    public function repost(int $id)
    {
        $post = $this->postService->repostPost($id);

        return response()->json($post);
    }
}
