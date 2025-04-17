<?php

namespace App\Services;

use App\Events\UserExperienceChanged;
use App\Models\Content\Achievement;
use App\Models\Content\Task;
use App\Models\Users\User;
use Illuminate\Support\Facades\Log;

class UserSettingsService
{
    public function createNotification(User $user)
    {
        $user->notificationSettings()->create([
            'email_enabled' => true,
            'push_enabled' => true,
            'notify_on_new_message' => true,
            'notify_on_new_follower' => true,
            'notify_on_post_like' => true,
            'notify_on_comment' => true,
            'notify_on_comment_like' => true,
            'notify_on_mention' => true
        ]);
    }

    public function createBalance(User $user)
    {
        $user->userBalance()->create([
            'balance' => 0.00,
            'currency' => 'USD',
        ]);

        $user->userBalance()->create([
            'balance' => 0.00,
            'currency' => 'RUB',
        ]);
    }

    public function attachTask(User $user)
    {
        $defaultTasks = Task::all();
        foreach ($defaultTasks as $task) {
            $user->tasks()->attach($task->id, ['progress' => 0, 'completed' => false]);
        }
        // Логирование присвоения заданий пользователю
        Log::info('Default tasks assigned to user', ['user_id' => $user->id]);
    }

    public function attachAchievement(User $user)
    {
        $achievement = Achievement::first();
        if ($achievement) {
            $user->achievements()->syncWithoutDetaching([$achievement->id]);
            $points = $achievement->points;
            $user->update(['experience' => $user->experience + $points]);

            // Логирование присвоения достижения и обновления опыта
            Log::info('Achievement assigned and experience updated', ['user_id' => $user->id, 'achievement_id' => $achievement->id, 'points' => $points]);

            event(new UserExperienceChanged($user));
        }
    }
}
