<?php

namespace App\Events\Posts;

use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие обновления поста
 */
class PostUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Обновленный пост
     *
     * @var Post
     */
    public Post $post;

    /**
     * Предыдущие данные поста
     *
     * @var array
     */
    public array $oldData;

    /**
     * Создать новый экземпляр события.
     *
     * @param Post $post
     * @param array $oldData
     * @return void
     */
    public function __construct(Post $post, array $oldData = [])
    {
        $this->post = $post;
        $this->oldData = $oldData;
    }
} 