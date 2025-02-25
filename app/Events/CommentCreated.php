<?php

namespace App\Events;

use App\Models\Comments\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
