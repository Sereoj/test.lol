<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Models\Posts\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PostPublishedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostPublished $event): void
    {
        $post = $event->post;

        if ($post->status === Post::STATUS_DRAFT) {
            $post->update(['status' => Post::STATUS_PUBLISHED]);
        }
    }
}
