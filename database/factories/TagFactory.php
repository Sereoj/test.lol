<?php

namespace Database\Factories;

use App\Models\Content\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(rand(1, 2), true);
        return [
            'name' => json_encode([
                'en' => $name,
                'ru' => fake()->unique()->words(rand(1, 2), true),
            ]),
            'slug' => Str::slug($name),
            'meta' => json_encode([
                'icon' => fake()->optional()->word(),
                'color' => fake()->optional()->hexColor(),
                'type' => fake()->randomElement(['common', 'style', 'medium', 'subject', 'technique']),
                'usage_count' => fake()->numberBetween(0, 1000),
                'description' => fake()->optional()->sentence()
            ]),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Create a popular tag.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => json_encode([
                'icon' => fake()->optional()->word(),
                'color' => fake()->optional()->hexColor(),
                'type' => fake()->randomElement(['common', 'style', 'medium', 'subject', 'technique']),
                'usage_count' => fake()->numberBetween(500, 5000),
                'description' => fake()->optional()->sentence()
            ]),
        ]);
    }

    /**
     * Create a style tag.
     */
    public function style(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => json_encode([
                'icon' => 'brush',
                'color' => fake()->hexColor(),
                'type' => 'style',
                'usage_count' => fake()->numberBetween(0, 1000),
                'description' => 'Художественный стиль: ' . fake()->word()
            ]),
        ]);
    }

    /**
     * Create a technique tag.
     */
    public function technique(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => json_encode([
                'icon' => 'tools',
                'color' => fake()->hexColor(),
                'type' => 'technique',
                'usage_count' => fake()->numberBetween(0, 1000),
                'description' => 'Техника исполнения: ' . fake()->word()
            ]),
        ]);
    }
} 