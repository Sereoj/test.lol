<?php

namespace Database\Seeders;

use App\Models\Users\User;
use App\Models\Users\UserWorkExperience;
use Illuminate\Database\Seeder;

class UserWorkExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем всех пользователей
        $users = User::all();

        foreach ($users as $user) {
            // Для каждого пользователя создаем от 1 до 5 записей опыта работы
            $experiencesCount = rand(1, 5);

            for ($i = 0; $i < $experiencesCount; $i++) {
                // Последняя запись может быть текущим местом работы
                $isCurrent = ($i === $experiencesCount - 1) && fake()->boolean(40);

                UserWorkExperience::factory()->create([
                    'user_id' => $user->id,
                    'is_current' => $isCurrent,
                ]);
            }
        }

        // Создаем несколько пользователей без опыта работы
        $usersWithoutExperience = User::inRandomOrder()->limit(5)->get();

        foreach ($usersWithoutExperience as $user) {
            // Удаляем существующий опыт работы
            UserWorkExperience::where('user_id', $user->id)->delete();

            // Создаем запись "Нет опыта работы"
            UserWorkExperience::factory()->noExperience()->create([
                'user_id' => $user->id,
            ]);
        }

        $this->command->info('User work experiences created successfully!');
    }
}
