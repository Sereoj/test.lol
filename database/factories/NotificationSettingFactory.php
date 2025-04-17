<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\NotificationSetting>
 */
class NotificationSettingFactory extends Factory
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
            'notify_on_new_message' => fake()->boolean(80),
            'notify_on_new_follower' => fake()->boolean(70),
            'notify_on_post_like' => fake()->boolean(60),
            'notify_on_comment' => fake()->boolean(70),
            'notify_on_comment_like' => fake()->boolean(50),
            'notify_on_mention' => fake()->boolean(90),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that all notifications are enabled.
     */
    public function allEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'notify_on_new_message' => true,
            'notify_on_new_follower' => true,
            'notify_on_post_like' => true,
            'notify_on_comment' => true,
            'notify_on_comment_like' => true,
            'notify_on_mention' => true,
        ]);
    }

    /**
     * Indicate that all notifications are disabled.
     */
    public function allDisabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'notify_on_new_message' => false,
            'notify_on_new_follower' => false,
            'notify_on_post_like' => false,
            'notify_on_comment' => false,
            'notify_on_comment_like' => false,
            'notify_on_mention' => false,
        ]);
    }

    /**
     * Indicate that only important notifications are enabled.
     */
    public function importantOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'notify_on_new_message' => true,
            'notify_on_new_follower' => false,
            'notify_on_post_like' => false,
            'notify_on_comment' => true,
            'notify_on_comment_like' => false,
            'notify_on_mention' => true,
        ]);
    }
}
