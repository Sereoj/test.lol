<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\ChallengeVote;
use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengeVote>
 */
class ChallengeVoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_id' => Challenge::factory(),
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }
}
