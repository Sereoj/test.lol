<?php

namespace Database\Factories;

use App\Models\Users\User;
use App\Models\Users\UserWorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\UserWorkExperience>
 */
class UserWorkExperienceFactory extends Factory
{
    protected $model = UserWorkExperience::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isCurrent = fake()->boolean(30);
        $startDate = fake()->dateTimeBetween('-10 years', '-1 year');
        $endDate = $isCurrent ? null : fake()->dateTimeBetween($startDate, 'now');

        return [
            'user_id' => User::factory(),
            'company' => fake()->company(),
            'position' => fake()->randomElement([
                'Junior Designer',
                'Middle Designer',
                'Senior Designer',
                'Lead Designer',
                'Art Director',
                'Creative Director',
                'Graphic Designer',
                'UI/UX Designer',
                'Motion Designer',
                'Illustrator',
                '3D Artist',
                'Concept Artist',
            ]),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => fake()->boolean(70) ? fake()->paragraph() : null,
            'is_current' => $isCurrent,
        ];
    }

    /**
     * Указать, что это текущее место работы.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
            'end_date' => null,
        ]);
    }

    /**
     * Указать, что это прошлое место работы.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => false,
            'end_date' => fake()->dateTimeBetween($attributes['start_date'] ?? '-1 year', 'now'),
        ]);
    }

    /**
     * Указать, что это опыт работы "Нет опыта работы".
     */
    public function noExperience(): static
    {
        return $this->state(fn (array $attributes) => [
            'company' => 'Нет опыта работы',
            'position' => 'Нет опыта работы',
            'start_date' => now(),
            'end_date' => null,
            'description' => null,
            'is_current' => true,
        ]);
    }
}
