<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', 'now');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');
        $status = $endDate > now() ? 'active' : 'expired';
        $planName = fake()->randomElement(['basic', 'premium', 'pro']);
        
        return [
            'user_id' => fake()->numberBetween(1, 20),
            'plan' => $planName,
            'status' => $status,
            'amount' => fake()->randomFloat(2, 5, 100),
            'currency' => 'USD',
            'started_at' => $startDate,
            'expires_at' => $endDate,
            'created_at' => $startDate,
            'updated_at' => fake()->dateTimeBetween($startDate, 'now'),
        ];
    }

    /**
     * Indicate that the subscription is active.
     */
    public function active(): static
    {
        $startDate = fake()->dateTimeBetween('-3 months', 'now');
        $endDate = fake()->dateTimeBetween('+1 day', '+6 months');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'started_at' => $startDate,
            'expires_at' => $endDate,
        ]);
    }

    /**
     * Indicate that the subscription is expired.
     */
    public function expired(): static
    {
        $startDate = fake()->dateTimeBetween('-1 year', '-1 month');
        $endDate = fake()->dateTimeBetween('-1 month', '-1 day');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'started_at' => $startDate,
            'expires_at' => $endDate,
        ]);
    }

    /**
     * Indicate that the subscription is for the basic plan.
     */
    public function basicPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'basic',
            'amount' => fake()->randomFloat(2, 5, 10),
        ]);
    }

    /**
     * Indicate that the subscription is for the premium plan.
     */
    public function premiumPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'premium',
            'amount' => fake()->randomFloat(2, 15, 30),
        ]);
    }

    /**
     * Indicate that the subscription is for the pro plan.
     */
    public function proPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan' => 'pro',
            'amount' => fake()->randomFloat(2, 40, 100),
        ]);
    }

    /**
     * Indicate that the subscription is canceled.
     */
    public function canceled(): static
    {
        $startDate = fake()->dateTimeBetween('-6 months', '-1 day');
        $cancelDate = fake()->dateTimeBetween($startDate, 'now');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'started_at' => $startDate,
            'expires_at' => $cancelDate,
        ]);
    }
} 