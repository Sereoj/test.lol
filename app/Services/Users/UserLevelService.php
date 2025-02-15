<?php

namespace App\Services\Users;

use App\Models\Users\UserLevel;

class UserLevelService
{
    /**
     * Создание нового уровня.
     */
    public function createLevel($name, $experienceRequired)
    {
        return UserLevel::query()->create([
            'name' => json_encode($name),
            'experience_required' => $experienceRequired,
        ]);
    }

    /**
     * Присваивание уровня пользователю.
     */
    public function assignLevelToUser($user, $levelId)
    {
        $level = UserLevel::query()->find($levelId);
        $user->level()->associate($level);
        $user->save();
    }

    /**
     * Получить уровень по ID.
     */
    public function getLevelById($id)
    {
        return UserLevel::query()->find($id);
    }

    /**
     * Получить все уровни.
     */
    public function getAllLevels()
    {
        return UserLevel::all();
    }
}
