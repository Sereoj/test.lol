<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Events\UserExperienceChanged;
use App\Models\Content\Achievement;

class UpdateUserExperience
{
    public function handle(TaskCompleted $event)
    {
        $user = $event->user;
        $task = $event->task;

        // Обновление опыта пользователя
        $experienceReward = $task->experience_reward;
        $user->update(['experience' => $user->experience + $experienceReward]);

        // Вызов события для обновления уровня пользователя
        event(new UserExperienceChanged($user));

        // Проверка на достижения
        $this->checkAchievements($user);
    }

    private function checkAchievements($user)
    {
        $achievements = Achievement::all();
        foreach ($achievements as $achievement) {
            // Пример проверки достижения
            if ($user->experience >= $achievement->points) {
                $user->achievements()->syncWithoutDetaching([$achievement->id]);
            }
        }
    }
}
