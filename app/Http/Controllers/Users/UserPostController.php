<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Media\ThumbMediaResource;
use App\Models\Users\User;
use App\Services\Posts\PostService;

// Контроллер для работы с постами пользователей
class UserPostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    // Получение списка постов пользователя
    public function index(User $user)
    {
        return ThumbMediaResource::collection($this->postService->getPostsByUser($user));
    }
}
