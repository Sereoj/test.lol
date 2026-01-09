<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Services\ChallengeService;
use App\Traits\LoggableTrait;
use Illuminate\Console\Command;

class FinishVoting extends Command
{
    use LoggableTrait;

    protected $signature = 'challenges:finish-voting';

    protected $description = 'Finish voting and determine winners for challenges';

    protected ChallengeService $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        parent::__construct();
        $this->challengeService = $challengeService;
    }

    public function handle(): int
    {
        $this->info('Checking for challenges to finish voting...');

        $challenges = Challenge::where('status', 'voting')
            ->where(function ($query) {
                $query->whereNull('voting_end_date')
                    ->orWhere('voting_end_date', '<=', now());
            })
            ->get();

        if ($challenges->isEmpty()) {
            $this->info('No challenges to finish voting.');
            return Command::SUCCESS;
        }

        $finishedCount = 0;

        foreach ($challenges as $challenge) {
            try {
                $this->challengeService->finishChallenge($challenge->id);

                $this->info("Finished voting for challenge ID {$challenge->id}: {$challenge->title}");

                $this->logInfo('Голосование челленджа автоматически завершено', [
                    'challenge_id' => $challenge->id,
                    'title' => $challenge->title,
                ]);

                $finishedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to finish voting for challenge ID {$challenge->id}: {$e->getMessage()}");

                $this->logError('Ошибка при автоматическом завершении голосования', [
                    'challenge_id' => $challenge->id,
                ], $e);
            }
        }

        $this->info("Finished voting for {$finishedCount} challenge(s).");

        return Command::SUCCESS;
    }
}
