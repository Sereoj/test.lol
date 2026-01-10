<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeVote;
use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChallengeVoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем челленджи в статусе voting или completed
        $challenges = Challenge::whereIn('status', ['voting', 'completed'])->get();

        foreach ($challenges as $challenge) {
            // Получаем посты, которые участвуют в этом челлендже
            $posts = Post::where('challenge_id', $challenge->id)
                ->where('status', 'published')
                ->get();

            if ($posts->isEmpty()) {
                continue;
            }

            // Получаем пользователей для голосования (исключая авторов постов)
            $voters = User::whereNotIn('id', $posts->pluck('user_id')->toArray())
                ->inRandomOrder()
                ->limit(rand(10, 30))
                ->get();

            foreach ($voters as $voter) {
                // Проверяем, что пользователь еще не голосовал в этом челлендже
                $existingVote = ChallengeVote::where('challenge_id', $challenge->id)
                    ->where('user_id', $voter->id)
                    ->exists();

                if (!$existingVote) {
                    // Выбираем случайный пост для голосования
                    $randomPost = $posts->random();

                    ChallengeVote::create([
                        'challenge_id' => $challenge->id,
                        'user_id' => $voter->id,
                        'post_id' => $randomPost->id,
                    ]);
                }
            }
        }

        $this->command->info('Challenge votes created successfully!');
    }
}
