<?php

namespace Database\Factories;

use App\Models\Follow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Follow>
 */
class FollowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Убеждаемся, что follower_id и following_id разные
        $follower = fake()->numberBetween(1, 20);
        $following = fake()->numberBetween(1, 20);
        
        while ($follower === $following) {
            $following = fake()->numberBetween(1, 20);
        }
        
        return [
            'follower_id' => $follower,
            'following_id' => $following,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }
} 