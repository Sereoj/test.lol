<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['topup', 'purchase', 'withdrawal', 'transfer']);
        $amount = fake()->randomFloat(2, 1, 500);
        
        return [
            'user_id' => fake()->numberBetween(1, 20),
            'type' => $type,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'metadata' => json_encode([
                'description' => $this->getDescriptionForType($type),
                'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'internal']),
                'ip_address' => fake()->ipv4(),
                'reference_id' => Str::uuid()
            ]),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Generate description based on transaction type.
     */
    private function getDescriptionForType(string $type): string
    {
        return match($type) {
            'purchase' => 'Покупка контента #' . fake()->numberBetween(1, 1000),
            'topup' => 'Пополнение баланса',
            'transfer' => 'Перевод пользователю #' . fake()->numberBetween(1, 20),
            'withdrawal' => 'Вывод средств',
            default => 'Транзакция #' . fake()->numberBetween(1, 1000),
        };
    }

    /**
     * Indicate that the transaction is a purchase.
     */
    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'purchase',
            'metadata' => json_encode([
                'description' => 'Покупка контента #' . fake()->numberBetween(1, 1000),
                'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'internal']),
                'ip_address' => fake()->ipv4(),
                'post_id' => fake()->numberBetween(1, 50),
                'reference_id' => Str::uuid()
            ]),
        ]);
    }

    /**
     * Indicate that the transaction is a withdrawal.
     */
    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'withdrawal',
            'metadata' => json_encode([
                'description' => 'Вывод средств',
                'payment_method' => fake()->randomElement(['paypal', 'bank_transfer']),
                'ip_address' => fake()->ipv4(),
                'reference_id' => Str::uuid()
            ]),
        ]);
    }

    /**
     * Indicate that the transaction is a deposit (topup).
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'topup',
            'metadata' => json_encode([
                'description' => 'Пополнение баланса',
                'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer']),
                'ip_address' => fake()->ipv4(),
                'gateway' => fake()->randomElement(['anypay', 'selection', 'enot', 'tinkoff']),
                'reference_id' => Str::uuid()
            ]),
        ]);
    }
} 