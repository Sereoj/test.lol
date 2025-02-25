<?php

namespace App\Events;

use App\Models\Posts\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostPublished
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Post $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
