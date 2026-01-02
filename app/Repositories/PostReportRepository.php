<?php

namespace App\Repositories;

use App\Models\Posts\PostReport;
use Exception;

class PostReportRepository
{
    protected PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function createReport($postId, int $userId, string $category, string $reason): PostReport
    {
        $post = $this->postRepository->getPost($postId);

        if ($post->user_id === $userId) {
            throw new Exception('Нельзя пожаловаться на собственный пост', 403);
        }

        $existingReport = PostReport::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingReport) {
            throw new Exception('Вы уже пожаловались на этот пост', 409);
        }

        return PostReport::create([
            'post_id' => $post->id,
            'user_id' => $userId,
            'category' => $category,
            'reason' => $reason,
            'status' => 'pending',
        ]);
    }

    public function findById(int $id): ?PostReport
    {
        return PostReport::find($id);
    }

    public function getUserReportForPost(int $postId, int $userId): ?PostReport
    {
        return PostReport::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();
    }
}
