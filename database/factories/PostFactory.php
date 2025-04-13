<?php

namespace Database\Factories;

use App\Models\Posts\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Posts\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $isFree = fake()->boolean(80); // 80% постов бесплатны

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'user_id' => fake()->numberBetween(1, 20), // предполагаем, что у нас есть 20 пользователей
            'content' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'is_adult_content' => fake()->boolean(15),
            'is_nsfl_content' => fake()->boolean(5),
            'has_copyright' => fake()->boolean(70),
            'price' => $isFree ? null : fake()->randomFloat(2, 1, 100),
            'is_free' => $isFree,
            'category_id' => fake()->numberBetween(1, 10),
            'meta' => json_encode([
                'views' => fake()->numberBetween(0, 1000),
                'likes' => fake()->numberBetween(0, 500),
                'featured' => fake()->boolean(10),
                'seo_title' => $title,
                'seo_description' => fake()->sentence(),
                'seo_keywords' => implode(',', fake()->words(5))
            ]),
            'settings' => json_encode([
                'comments_enabled' => fake()->boolean(95),
                'downloads_enabled' => fake()->boolean(80),
                'show_stats' => fake()->boolean(90)
            ]),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the post is draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the post is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free' => true,
            'price' => null,
        ]);
    }

    /**
     * Indicate that the post is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_free' => false,
            'price' => fake()->randomFloat(2, 1, 100),
        ]);
    }
} 