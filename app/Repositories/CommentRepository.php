<?php

namespace App\Repositories;

use App\Events\CommentCreated;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentLike;
use App\Models\Comments\CommentReport;
use App\Models\Comments\CommentRepost;
use App\Models\Posts\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentRepository
{
    public function findCommentById($id)
    {
        return Comment::find($id);
    }

    public function findCommentByIdWithTrashed($id)
    {
        return Comment::withTrashed()->find($id);
    }

    public function getCommentsForPost($postId, $page = 1, $limit = 10, $sortBy = 'created_at', $order = 'desc')
    {
        $userId = auth()->check() ? auth()->id() : null;

        $query = Comment::withTrashed()->with(['user']);

        if ($userId) {
            $query->with([
                'likes' => function($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'reports' => function($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'reposts' => function($query) use ($userId) {
                    $query->where('user_id', $userId);
                }
            ]);
        }

        $query->withCount(['likes', 'reports', 'reposts']);

        $query->with(['replies' => function($query) use ($userId) {
            $query->withTrashed();
            $query->with(['user']);

            if ($userId) {
                $query->with([
                    'likes' => function($q) use ($userId) {
                        $q->where('user_id', $userId);
                    },
                    'reports' => function($q) use ($userId) {
                        $q->where('user_id', $userId);
                    },
                    'reposts' => function($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }
                ]);
            }

            $query->withCount(['likes', 'reports', 'reposts']);
        }]);

        return $query->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy($sortBy, $order)
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function findParentComment($parentId)
    {
        return Comment::find($parentId);
    }

    public function createComment(array $data)
    {
        $comment = Comment::create($data);
        event(new CommentCreated($comment));

        return $comment;
    }

    public function updateComment(Comment $comment, array $data)
    {
        $comment->update($data);
        return $comment;
    }

    public function deleteComment(Comment $comment)
    {
        Log::info('deleteComment', [
            'comment' => $comment
        ]);
        return $comment->delete();
    }

    public function updateOrCreateReaction($commentId, $userId, $type)
    {
        return DB::transaction(function () use ($commentId, $userId, $type) {
            Log::info("Reacting to comment {$commentId} with type {$type} by user " . $userId);

            $existingReaction = CommentLike::where([
                'comment_id' => $commentId,
                'user_id' => $userId,
                'type' => $type
            ])->first();

            if ($existingReaction) {
                return $existingReaction->load('user');
            }

            // Иначе создаем или обновляем
            return CommentLike::with(['user'])->updateOrCreate(
                ['comment_id' => $commentId, 'user_id' => $userId],
                ['type' => $type]
            );
        });
    }

    public function updateOrCreateReport($commentId, $userId, $reason)
    {
        return CommentReport::updateOrCreate(
            ['comment_id' => $commentId, 'user_id' => $userId],
            ['reason' => $reason]
        );
    }

    public function updateOrCreateRepost($commentId, $userId)
    {
        return CommentRepost::updateOrCreate(
            ['comment_id' => $commentId, 'user_id' => $userId]
        );
    }
}
