<?php

namespace Database\Factories;

use App\Models\PostStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostStatistic>
 */
class PostStatisticFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => fake()->unique()->numberBetween(1, 50),
            'views_count' => fake()->numberBetween(0, 5000),
            'likes_count' => fake()->numberBetween(0, 500),
            'reposts_count' => fake()->numberBetween(0, 100),
            'downloads_count' => fake()->numberBetween(0, 300),
            'purchases_count' => fake()->numberBetween(0, 50),
            'comments_count' => fake()->numberBetween(0, 200),
            'impressions_count' => fake()->numberBetween(0, 10000),
            'clicks_count' => fake()->numberBetween(0, 2000),
            'shares_count' => fake()->numberBetween(0, 150),
            'engagement_score' => fake()->numberBetween(0, 100),
            'last_interaction_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'created_at' => fake()->dateTimeBetween('-1 year', '-1 month'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Create a high-engagement post statistic.
     */
    public function highEngagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => fake()->numberBetween(1000, 10000),
            'likes_count' => fake()->numberBetween(100, 1000),
            'comments_count' => fake()->numberBetween(50, 500),
            'impressions_count' => fake()->numberBetween(5000, 20000),
            'engagement_score' => fake()->numberBetween(70, 100),
        ]);
    }

    /**
     * Create a low-engagement post statistic.
     */
    public function lowEngagement(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => fake()->numberBetween(0, 100),
            'likes_count' => fake()->numberBetween(0, 10),
            'comments_count' => fake()->numberBetween(0, 5),
            'impressions_count' => fake()->numberBetween(0, 500),
            'engagement_score' => fake()->numberBetween(0, 20),
        ]);
    }

    /**
     * Create a viral post statistic.
     */
    public function viral(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => fake()->numberBetween(10000, 100000),
            'likes_count' => fake()->numberBetween(1000, 10000),
            'reposts_count' => fake()->numberBetween(500, 5000),
            'shares_count' => fake()->numberBetween(500, 5000),
            'comments_count' => fake()->numberBetween(500, 3000),
            'impressions_count' => fake()->numberBetween(50000, 500000),
            'engagement_score' => fake()->numberBetween(90, 100),
        ]);
    }
} 