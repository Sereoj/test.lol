<?php

namespace App\Events\Posts;

use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие создания поста
 */
class PostCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Созданный пост
     *
     * @var Post
     */
    public Post $post;

    /**
     * Создать новый экземпляр события.
     *
     * @param Post $post
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }
} 