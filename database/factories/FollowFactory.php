<?php

namespace Database\Factories;

use App\Models\Follow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Follow>
 */
class FollowFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Follow::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Генерируем уникальную комбинацию follower_id и following_id
        static $usedPairs = [];

        do {
            $follower = fake()->numberBetween(1, 20);
            $following = fake()->numberBetween(1, 20);
            $pair = $follower . '-' . $following;
        } while ($follower === $following || isset($usedPairs[$pair]));

        $usedPairs[$pair] = true;

        return [
            'follower_id' => $follower,
            'following_id' => $following,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }
}
