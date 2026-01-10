<?php

namespace App\Events;

use App\Models\Posts\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCollaboratorAdded
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Post $post;
    public array $collaboratorIds;

    public function __construct(Post $post, array $collaboratorIds)
    {
        $this->post = $post;
        $this->collaboratorIds = $collaboratorIds;
    }
}
