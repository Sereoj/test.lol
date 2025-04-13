<?php

namespace Database\Factories;

use App\Models\Users\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\UserSetting>
 */
class UserSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_online' => fake()->boolean(30),
            'is_preferences_feed' => fake()->boolean(50),
            'preferences_feed' => fake()->randomElement(['popularity', 'downloads', 'likes', 'default']),
            'is_private' => fake()->boolean(20),
            'enable_two_factor' => fake()->boolean(10),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the user settings enable privacy.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
            'enable_two_factor' => true,
        ]);
    }

    /**
     * Indicate that the user is online.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_online' => true,
        ]);
    }

    /**
     * Indicate that the user prefers content by popularity.
     */
    public function popularContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_preferences_feed' => true,
            'preferences_feed' => 'popularity',
        ]);
    }
} 