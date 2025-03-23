<?php

namespace App\Events\Comments;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие удаления комментария
 */
class CommentDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Комментарий перед удалением
     *
     * @var Comment
     */
    public Comment $comment;

    /**
     * ID удаленного комментария
     *
     * @var int
     */
    public int $commentId;

    /**
     * ID поста, к которому относился комментарий
     *
     * @var int
     */
    public int $postId;

    /**
     * Создать новый экземпляр события.
     *
     * @param Comment $comment
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->commentId = $comment->id;
        $this->postId = $comment->post_id;
    }
} 