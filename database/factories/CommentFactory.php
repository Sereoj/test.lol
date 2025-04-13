<?php

namespace Database\Factories;

use App\Models\Comments\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comments\Comment>
 */
class CommentFactory extends Factory
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
            'post_id' => fake()->numberBetween(1, 50),
            'parent_id' => null,
            'content' => fake()->paragraph(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the comment is a reply to another comment.
     */
    public function asReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => fake()->numberBetween(1, 30),
        ]);
    }

    /**
     * Indicate that the comment has a short content.
     */
    public function shortContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the comment has a long content.
     */
    public function longContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => fake()->paragraphs(3, true),
        ]);
    }
} 