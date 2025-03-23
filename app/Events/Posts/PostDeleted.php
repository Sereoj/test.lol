<?php

namespace App\Events\Posts;

use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие удаления поста
 */
class PostDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Пост перед удалением
     *
     * @var Post
     */
    public Post $post;

    /**
     * ID удаленного поста
     *
     * @var int
     */
    public int $postId;

    /**
     * Создать новый экземпляр события.
     *
     * @param Post $post
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
        $this->postId = $post->id;
    }
} 