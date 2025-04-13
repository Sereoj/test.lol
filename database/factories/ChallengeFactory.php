<?php

namespace Database\Factories;

use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+2 months');
        $prize = fake()->numberBetween(100, 5000);
        
        return [
            'title' => $title,
            'description' => fake()->paragraphs(3, true),
            'cover_path' => 'media/challenges/' . Str::slug($title) . '.jpg',
            'prize_amount' => $prize,
            'prize_currency' => 'USD',
            'participants_count' => fake()->numberBetween(0, 100),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->getChallengeStatus($startDate, $endDate),
            'created_at' => fake()->dateTimeBetween('-3 months', $startDate),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Determine challenge status based on dates.
     */
    private function getChallengeStatus(\DateTime $startDate, \DateTime $endDate): string
    {
        $now = now();
        
        if ($now < $startDate) {
            return 'draft';
        } elseif ($now >= $startDate && $now <= $endDate) {
            return 'active';
        } else {
            return 'completed';
        }
    }

    /**
     * Create an active challenge.
     */
    public function active(): static
    {
        $startDate = fake()->dateTimeBetween('-1 month', '-1 day');
        $endDate = fake()->dateTimeBetween('+1 day', '+1 month');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);
    }

    /**
     * Create a draft challenge.
     */
    public function draft(): static
    {
        $startDate = fake()->dateTimeBetween('+1 day', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+2 months');
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'draft',
        ]);
    }

    /**
     * Create a completed challenge.
     */
    public function completed(): static
    {
        $endDate = fake()->dateTimeBetween('-3 months', '-1 day');
        $startDate = fake()->dateTimeBetween('-6 months', $endDate);
        
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'completed',
        ]);
    }

    /**
     * Create a cancelled challenge.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
} 