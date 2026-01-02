<?php

namespace App\Repositories;

use App\Models\Posts\Post;
use App\Models\Posts\PostReport;
use Exception;

class PostReportRepository
{
    public function createReport(int $postId, int $userId, string $category, string $reason): PostReport
    {
        $post = Post::findOrFail($postId);

        if ($post->user_id === $userId) {
            throw new Exception('Нельзя пожаловаться на собственный пост', 403);
        }

        $existingReport = PostReport::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReport) {
            throw new Exception('Вы уже пожаловались на этот пост', 409);
        }

        return PostReport::create([
            'post_id' => $postId,
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
