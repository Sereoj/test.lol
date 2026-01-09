<?php

namespace App\Events\Challenges;

use App\Models\Challenge;
use App\Models\ChallengeVote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCasted
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Challenge $challenge;
    public ChallengeVote $vote;

    public function __construct(Challenge $challenge, ChallengeVote $vote)
    {
        $this->challenge = $challenge;
        $this->vote = $vote;
    }
}
