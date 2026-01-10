<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengePrize;
use App\Models\ChallengeVote;
use App\Models\ChallengeWinner;
use App\Models\Posts\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChallengeWinnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем только завершенные челленджи
        $challenges = Challenge::where('status', 'completed')->get();

        foreach ($challenges as $challenge) {
            // Получаем призы для челленджа
            $prizes = ChallengePrize::where('challenge_id', $challenge->id)
                ->orderBy('place')
                ->get();

            if ($prizes->isEmpty()) {
                continue;
            }

            // Получаем топ постов по голосам
            $topPosts = Post::where('challenge_id', $challenge->id)
                ->where('status', 'published')
                ->withCount('challengeVotes')
                ->orderByDesc('challenge_votes_count')
                ->limit($prizes->count())
                ->get();

            if ($topPosts->isEmpty()) {
                continue;
            }

            // Создаем победителей для каждого места
            foreach ($prizes as $index => $prize) {
                if (!isset($topPosts[$index])) {
                    break;
                }

                $post = $topPosts[$index];
                $payoutStatus = fake()->randomElement(['pending', 'processing', 'completed', 'failed']);

                $winnerData = [
                    'challenge_id' => $challenge->id,
                    'user_id' => $post->user_id,
                    'post_id' => $post->id,
                    'place' => $prize->place,
                    'prize_amount' => $prize->amount,
                    'prize_currency' => $challenge->prize_currency ?? 'RUB',
                    'payout_status' => $payoutStatus,
                    'transaction_id' => null,
                    'payout_completed_at' => $payoutStatus === 'completed' ? fake()->dateTimeBetween('-1 month', 'now') : null,
                ];

                ChallengeWinner::create($winnerData);
            }
        }

        $this->command->info('Challenge winners created successfully!');
    }
}
