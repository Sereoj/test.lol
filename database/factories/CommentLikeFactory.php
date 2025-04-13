<?php

namespace Database\Factories;

use App\Models\Comments\CommentLike;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comments\CommentLike>
 */
class CommentLikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1, 20),
            'comment_id' => fake()->numberBetween(1, 80),
            'type' => fake()->randomElement(['like', 'dislike']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the reaction is a like.
     */
    public function like(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'like',
        ]);
    }

    /**
     * Indicate that the reaction is a dislike.
     */
    public function dislike(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'dislike',
        ]);
    }
} 