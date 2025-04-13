<?php

namespace Database\Factories;

use App\Models\Content\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content\Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(rand(1, 3), true);
        
        return [
            'name' => json_encode([
                'en' => $name, 
                'ru' => fake()->unique()->words(rand(1, 3), true)
            ]),
            'description' => json_encode([
                'en' => fake()->paragraph(),
                'ru' => fake()->paragraph()
            ]),
            'points' => fake()->numberBetween(10, 100),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Create a high-value achievement.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(100, 500),
        ]);
    }

    /**
     * Create a low-value achievement.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(1, 10),
        ]);
    }
} 