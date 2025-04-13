<?php

namespace Database\Factories;

use App\Models\Categories\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categories\Category>
 */
class CategoryFactory extends Factory
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
                'ru' => fake()->unique()->words(rand(1, 3), true),
            ]),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'meta' => json_encode([
                'icon' => fake()->optional()->word(),
                'color' => fake()->optional()->hexColor(),
                'order' => fake()->optional()->numberBetween(1, 10),
                'parent_id' => fake()->optional()->numberBetween(1, 5),
                'is_featured' => fake()->boolean(20),
                'image_path' => fake()->optional()->imageUrl(),
            ]),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Create a root category.
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => json_encode([
                'icon' => fake()->word(),
                'color' => fake()->hexColor(),
                'order' => fake()->numberBetween(1, 5),
                'parent_id' => null,
                'is_featured' => true,
                'image_path' => fake()->imageUrl(),
            ]),
        ]);
    }

    /**
     * Create a subcategory.
     */
    public function subcategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => json_encode([
                'icon' => fake()->optional()->word(),
                'color' => fake()->optional()->hexColor(),
                'order' => fake()->numberBetween(6, 20),
                'parent_id' => fake()->numberBetween(1, 5),
                'is_featured' => false,
                'image_path' => fake()->optional()->imageUrl(),
            ]),
        ]);
    }

    /**
     * Create a featured category.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => json_encode([
                'icon' => fake()->word(),
                'color' => fake()->hexColor(),
                'order' => fake()->numberBetween(1, 3),
                'parent_id' => null,
                'is_featured' => true,
                'image_path' => fake()->imageUrl(),
            ]),
        ]);
    }
} 