<?php

namespace App\Events\Challenges;

use App\Models\Challenge;
use App\Models\Posts\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmissionCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Challenge $challenge;
    public Post $post;

    public function __construct(Challenge $challenge, Post $post)
    {
        $this->challenge = $challenge;
        $this->post = $post;
    }
}
