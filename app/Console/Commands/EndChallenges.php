<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use App\Traits\LoggableTrait;
use Illuminate\Console\Command;

class EndChallenges extends Command
{
    use LoggableTrait;

    protected $signature = 'challenges:end';

    protected $description = 'End submission period for challenges that have reached their end date';

    public function handle(): int
    {
        $this->info('Checking for challenges to end...');

        $challenges = Challenge::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now())
            ->get();

        if ($challenges->isEmpty()) {
            $this->info('No challenges to end.');
            return Command::SUCCESS;
        }

        $endedCount = 0;

        foreach ($challenges as $challenge) {
            try {
                $newStatus = match ($challenge->winner_selection_method) {
                    'manual' => 'selecting_winners',
                    'voting_public', 'voting_participants' => 'voting',
                    default => 'selecting_winners',
                };

                $challenge->update(['status' => $newStatus]);

                $this->info("Ended challenge ID {$challenge->id}: {$challenge->title} (new status: {$newStatus})");

                $this->logInfo('Челлендж автоматически переведен в следующую стадию', [
                    'challenge_id' => $challenge->id,
                    'title' => $challenge->title,
                    'new_status' => $newStatus,
                ]);

                $endedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to end challenge ID {$challenge->id}: {$e->getMessage()}");

                $this->logError('Ошибка при автоматическом завершении челленджа', [
                    'challenge_id' => $challenge->id,
                ], $e);
            }
        }

        $this->info("Ended {$endedCount} challenge(s).");

        return Command::SUCCESS;
    }
}
