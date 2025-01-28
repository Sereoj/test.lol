<?php

namespace App\Services;

use App\Http\Resources\PostResource;
use App\Repositories\PostRepository;

class PostService
{
    private PostStatisticsService $statService;

    private PostRepository $postRepository;

    public function __construct(PostStatisticsService $statService, PostRepository $postRepository)
    {
        $this->statService = $statService;
        $this->postRepository = $postRepository;
    }

    public function getPosts(array $filters, $userId = null)
    {
        $posts = $this->postRepository->getPosts($filters, $userId);

        return PostResource::collection($posts);
    }

    public function getPost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        if (! $post) {
            return ['message' => 'Post not found.'];
        }

        $this->statService->incrementViews($post->id);

        return new PostResource($post);
    }

    public function createPost(array $data)
    {
        return $this->postRepository->createPost($data);
    }

    public function updatePost(int $id, array $data)
    {
        return $this->postRepository->updatePost($id, $data);
    }

    public function deletePost(int $id): void
    {
        $this->postRepository->deletePost($id);
    }

    public function likePost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        return $this->statService->incrementLikes($post->id);
    }

    public function unlikePost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        return $this->statService->decrementLikes($post->id);
    }

    public function repostPost(int $id)
    {
        $post = $this->postRepository->getPost($id);

        return $this->statService->repostPost($post->id);
    }
}
