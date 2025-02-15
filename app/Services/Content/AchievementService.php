<?php

namespace App\Services\Content;

use App\Models\Content\Achievement;
use App\Models\Users\User;

class AchievementService
{
    /**
     * Присвоить достижение пользователю.
     */
    public function assignAchievementToUser(User $user, Achievement $achievement)
    {
        if (! $user->achievements->contains($achievement)) {
            $user->achievements()->attach($achievement);
        }
    }

    /**
     * Удалить достижение у пользователя.
     */
    public function removeAchievementFromUser(User $user, Achievement $achievement)
    {
        if ($user->achievements->contains($achievement)) {
            $user->achievements()->detach($achievement);
        }
    }
}
