<?php

namespace App\Events\Challenges;

use App\Models\Challenge;
use App\Models\ChallengeWinner;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WinnerSelected
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Challenge $challenge;
    public ChallengeWinner $winner;

    public function __construct(Challenge $challenge, ChallengeWinner $winner)
    {
        $this->challenge = $challenge;
        $this->winner = $winner;
    }
}
