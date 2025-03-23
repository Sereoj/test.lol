<?php

namespace App\Events\Comments;

use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие обновления комментария
 */
class CommentUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Обновленный комментарий
     *
     * @var Comment
     */
    public Comment $comment;

    /**
     * Предыдущие данные комментария
     *
     * @var array
     */
    public array $oldData;

    /**
     * Создать новый экземпляр события.
     *
     * @param Comment $comment
     * @param array $oldData
     * @return void
     */
    public function __construct(Comment $comment, array $oldData = [])
    {
        $this->comment = $comment;
        $this->oldData = $oldData;
    }
} 