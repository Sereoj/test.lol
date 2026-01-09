<?php

namespace App\Events\Challenges;

use App\Models\Challenge;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChallengeStarted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Challenge $challenge;

    public function __construct(Challenge $challenge)
    {
        $this->challenge = $challenge;
    }
}
