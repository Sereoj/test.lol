<?php

namespace Database\Factories;

use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media\Media>
 */
class MediaFactory extends Factory
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
            'uuid' => Str::uuid(),
            'name' => fake()->words(3, true),
            'file_path' => 'uploads/images/' . fake()->uuid() . '.jpg',
            'type' => 'original',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(50000, 2000000), // 50KB - 2MB
            'width' => fake()->numberBetween(800, 1920),
            'height' => fake()->numberBetween(600, 1080),
            'is_public' => true,
            'parent_id' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the media is an image.
     */
    public function image(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'file_path' => 'uploads/images/' . fake()->uuid() . '.jpg',
                'mime_type' => 'image/jpeg',
                'type' => 'original',
                'width' => fake()->numberBetween(800, 1920),
                'height' => fake()->numberBetween(600, 1080),
            ];
        });
    }

    /**
     * Indicate that the media is a video.
     */
    public function video(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'file_path' => 'uploads/videos/' . fake()->uuid() . '.mp4',
                'mime_type' => 'video/mp4',
                'type' => 'original',
                'size' => fake()->numberBetween(1000000, 50000000), // 1MB - 50MB
                'width' => fake()->numberBetween(640, 1920),
                'height' => fake()->numberBetween(480, 1080),
            ];
        });
    }

    /**
     * Indicate that the media is a resized version.
     */
    public function resized(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'resized',
                'parent_id' => fake()->numberBetween(1, 10),
                'width' => fake()->numberBetween(200, 600),
                'height' => fake()->numberBetween(200, 600),
                'size' => fake()->numberBetween(10000, 500000), // 10KB - 500KB
                'file_path' => 'uploads/images/resized/' . fake()->uuid() . '.jpg',
            ];
        });
    }

    /**
     * Indicate that the media is a compressed version.
     */
    public function compressed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'compressed',
                'parent_id' => fake()->numberBetween(1, 10),
                'size' => fake()->numberBetween(5000, 100000), // 5KB - 100KB
                'file_path' => 'uploads/images/compressed/' . fake()->uuid() . '.jpg',
                'width' => fake()->numberBetween(400, 1200),
                'height' => fake()->numberBetween(300, 900),
            ];
        });
    }

    /**
     * Indicate that the media is a blur version.
     */
    public function blur(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'blur',
                'parent_id' => fake()->numberBetween(1, 10),
                'size' => fake()->numberBetween(1000, 20000), // 1KB - 20KB
                'file_path' => 'uploads/images/blur/' . fake()->uuid() . '.jpg',
                'width' => fake()->numberBetween(50, 200),
                'height' => fake()->numberBetween(50, 150),
            ];
        });
    }
} 