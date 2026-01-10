<?php

namespace Database\Factories;

use App\Models\Billing\Transaction;
use App\Models\Challenge;
use App\Models\ChallengeWinner;
use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengeWinner>
 */
class ChallengeWinnerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = ChallengeWinner::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $place = fake()->numberBetween(1, 5);
        $prizeAmount = $this->getPrizeAmountByPlace($place);

        return [
            'challenge_id' => Challenge::factory(),
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'place' => $place,
            'prize_amount' => $prizeAmount,
            'prize_currency' => 'RUB',
            'payout_status' => 'pending',
            'transaction_id' => null,
            'payout_completed_at' => null,
        ];
    }

    /**
     * Определить сумму приза в зависимости от места.
     */
    private function getPrizeAmountByPlace(int $place): float
    {
        return match ($place) {
            1 => fake()->randomFloat(2, 5000, 15000),
            2 => fake()->randomFloat(2, 3000, 8000),
            3 => fake()->randomFloat(2, 1000, 5000),
            default => fake()->randomFloat(2, 500, 2000),
        };
    }

    /**
     * Указать, что выплата в ожидании.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payout_status' => 'pending',
            'transaction_id' => null,
            'payout_completed_at' => null,
        ]);
    }

    /**
     * Указать, что выплата в обработке.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'payout_status' => 'processing',
            'transaction_id' => Transaction::factory(),
            'payout_completed_at' => null,
        ]);
    }

    /**
     * Указать, что выплата завершена.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payout_status' => 'completed',
            'transaction_id' => Transaction::factory(),
            'payout_completed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Указать, что выплата провалилась.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payout_status' => 'failed',
            'transaction_id' => null,
            'payout_completed_at' => null,
        ]);
    }

    /**
     * Создать победителя для первого места.
     */
    public function firstPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place' => 1,
            'prize_amount' => fake()->randomFloat(2, 5000, 15000),
        ]);
    }

    /**
     * Создать победителя для второго места.
     */
    public function secondPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place' => 2,
            'prize_amount' => fake()->randomFloat(2, 3000, 8000),
        ]);
    }

    /**
     * Создать победителя для третьего места.
     */
    public function thirdPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place' => 3,
            'prize_amount' => fake()->randomFloat(2, 1000, 5000),
        ]);
    }
}
