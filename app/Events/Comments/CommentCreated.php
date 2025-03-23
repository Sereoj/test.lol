<?php

namespace App\Events\Comments;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие создания комментария
 */
class CommentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Созданный комментарий
     *
     * @var Comment
     */
    public Comment $comment;

    /**
     * Создать новый экземпляр события.
     *
     * @param Comment $comment
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
} 