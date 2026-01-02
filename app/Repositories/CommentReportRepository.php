<?php

namespace App\Repositories;

use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use Exception;

class CommentReportRepository
{
    public function createReport(int $commentId, int $userId, string $category, string $reason): CommentReport
    {
        $comment = Comment::findOrFail($commentId);

        if ($comment->user_id === $userId) {
            throw new Exception('Нельзя пожаловаться на собственный комментарий', 403);
        }

        $existingReport = CommentReport::where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReport) {
            throw new Exception('Вы уже пожаловались на этот комментарий', 409);
        }

        return CommentReport::create([
            'comment_id' => $commentId,
            'user_id' => $userId,
            'category' => $category,
            'reason' => $reason,
            'status' => 'pending',
        ]);
    }

    public function findById(int $id): ?CommentReport
    {
        return CommentReport::find($id);
    }

    public function getUserReportForComment(int $commentId, int $userId): ?CommentReport
    {
        return CommentReport::where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->first();
    }
}
