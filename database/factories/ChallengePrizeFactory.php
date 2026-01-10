<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\ChallengePrize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengePrize>
 */
class ChallengePrizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $place = fake()->numberBetween(1, 5);
        $percentage = $this->getPercentageByPlace($place);

        return [
            'challenge_id' => Challenge::factory(),
            'place' => $place,
            'percentage' => $percentage,
            'amount' => 0,
        ];
    }

    /**
     * Определить процент приза в зависимости от места.
     */
    private function getPercentageByPlace(int $place): float
    {
        return match ($place) {
            1 => 50.00,
            2 => 30.00,
            3 => 20.00,
            default => 10.00,
        };
    }

    /**
     * Создать приз для первого места.
     */
    public function firstPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place' => 1,
            'percentage' => 50.00,
        ]);
    }

    /**
     * Создать приз для второго места.
     */
    public function secondPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place' => 2,
            'percentage' => 30.00,
        ]);
    }

    /**
     * Создать приз для третьего места.
     */
    public function thirdPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place' => 3,
            'percentage' => 20.00,
        ]);
    }
}
