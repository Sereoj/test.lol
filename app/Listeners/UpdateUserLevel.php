<?php

namespace App\Listeners;

use App\Events\UserExperienceChanged;
use App\Models\Users\UserLevel;

class UpdateUserLevel
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserExperienceChanged $event): void
    {
        $user = $event->user;
        $userExperience = $user->experience;

        $level = UserLevel::where('experience_required', '<=', $userExperience)
            ->orderBy('experience_required', 'desc')
            ->first();

        if ($level) {
            $user->level()->associate($level);
            $user->save();
        }
    }
}
