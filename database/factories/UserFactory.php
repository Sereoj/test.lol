<?php

namespace Database\Factories;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = fake()->userName();
        return [
            'username' => $username,
            'slug' => Str::slug($username) . '-' . Str::random(5),
            'description' => fake()->paragraph(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'verification' => fake()->boolean(20), // 20% вероятность быть верифицированным
            'experience' => fake()->numberBetween(0, 1000),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'language' => fake()->randomElement(['ru', 'en', 'fr', 'de']),
            'age' => fake()->numberBetween(18, 60),
            'password' => static::$password ??= Hash::make('password'),
            'provider' => null,
            'provider_id' => null,
            'level_id' => fake()->numberBetween(1, 5),
            'role_id' => 2, // Обычный пользователь по умолчанию
            'userSettings_id' => fake()->numberBetween(1, 3),
            'usingApps_id' => null,
            'status_id' => fake()->numberBetween(1, 3),
            'location_id' => fake()->numberBetween(1, 10),
            'employment_status_id' => fake()->numberBetween(1, 5),
            'remember_token' => Str::random(10),
            'seo_meta' => json_encode([
                'title' => 'Профиль художника - ' . $username,
                'description' => fake()->sentence(),
                'keywords' => implode(',', fake()->words(5))
            ]),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 1,
        ]);
    }

    /**
     * Indicate that the user is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification' => true,
        ]);
    }
}
