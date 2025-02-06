<?php

namespace App\Repositories;

use App\Events\CommentCreated;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentLike;
use App\Models\Comments\CommentReport;
use App\Models\Comments\CommentRepost;

class CommentRepository
{
    public function findCommentById($id)
    {
        return Comment::find($id);
    }

    public function getCommentsForPost($postId)
    {
        return Comment::with(['replies', 'likes', 'reports'])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->get();
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
        return $comment->delete();
    }

    public function updateOrCreateReaction($commentId, $userId, $type)
    {
        return CommentLike::updateOrCreate(
            ['comment_id' => $commentId, 'user_id' => $userId],
            ['type' => $type]
        );
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
