<?php

namespace App\Console\Commands;

use App\Events\Challenges\ChallengeStarted;
use App\Models\Challenge;
use App\Traits\LoggableTrait;
use Illuminate\Console\Command;

class StartChallenges extends Command
{
    use LoggableTrait;

    protected $signature = 'challenges:start';

    protected $description = 'Start challenges that have reached their start date';

    public function handle(): int
    {
        $this->info('Checking for challenges to start...');

        $challenges = Challenge::where('status', 'draft')
            ->whereNotNull('start_date')
            ->where('start_date', '<=', now())
            ->get();

        if ($challenges->isEmpty()) {
            $this->info('No challenges to start.');
            return Command::SUCCESS;
        }

        $startedCount = 0;

        foreach ($challenges as $challenge) {
            try {
                $challenge->update(['status' => 'active']);

                event(new ChallengeStarted($challenge));

                $this->info("Started challenge ID {$challenge->id}: {$challenge->title}");

                $this->logInfo('Челлендж автоматически запущен', [
                    'challenge_id' => $challenge->id,
                    'title' => $challenge->title,
                ]);

                $startedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to start challenge ID {$challenge->id}: {$e->getMessage()}");

                $this->logError('Ошибка при автоматическом запуске челленджа', [
                    'challenge_id' => $challenge->id,
                ], $e);
            }
        }

        $this->info("Started {$startedCount} challenge(s).");

        return Command::SUCCESS;
    }
}
