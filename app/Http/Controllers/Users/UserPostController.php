<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Media\ThumbMediaResource;
use App\Http\Resources\ThumbUserMediaResource;
use App\Models\Users\User;
use App\Services\Posts\PostService;
use Illuminate\Http\Request;

// Контроллер для работы с постами пользователей
class UserPostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    // Получение списка постов пользователя
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
