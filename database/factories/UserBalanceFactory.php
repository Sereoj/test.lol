<?php

namespace Database\Factories;

use App\Models\Users\UserBalance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\UserBalance>
 */
class UserBalanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->unique()->numberBetween(1, 20),
            'balance' => fake()->randomFloat(2, 0, 1000),
            'pending_balance' => fake()->randomFloat(2, 0, 500),
            'currency' => 'USD',
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the user has high balance.
     */
    public function highBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => fake()->randomFloat(2, 1000, 5000),
            'pending_balance' => fake()->randomFloat(2, 500, 2000),
        ]);
    }

    /**
     * Indicate that the user has no balance.
     */
    public function noBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0,
            'pending_balance' => 0,
        ]);
    }

    /**
     * Indicate that the user has USD currency.
     */
    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USD',
        ]);
    }

    /**
     * Indicate that the user has EUR currency.
     */
    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'EUR',
        ]);
    }
} 