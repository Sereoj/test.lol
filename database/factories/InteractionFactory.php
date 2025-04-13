<?php

namespace Database\Factories;

use App\Models\Interactions\Interaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interactions\Interaction>
 */
class InteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1, 20),
            'post_id' => fake()->numberBetween(1, 50),
            'interaction_type' => fake()->randomElement(['view', 'like', 'download']),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the interaction is a view.
     */
    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'view',
        ]);
    }

    /**
     * Indicate that the interaction is a like.
     */
    public function like(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'like',
        ]);
    }

    /**
     * Indicate that the interaction is a download.
     */
    public function download(): static
    {
        return $this->state(fn (array $attributes) => [
            'interaction_type' => 'download',
        ]);
    }
} 