<?php

namespace App\Services;

use App\Models\Level;

class LevelService
{
    /**
     * Создание нового уровня.
     */
    public function createLevel($name, $experienceRequired)
    {
        return Level::query()->create([
            'name' => json_encode($name),
            'experience_required' => $experienceRequired,
        ]);
    }

    /**
     * Присваивание уровня пользователю.
     */
    public function assignLevelToUser($user, $levelId)
    {
        $level = Level::query()->find($levelId);
        $user->level()->associate($level);
        $user->save();
    }

    /**
     * Получить уровень по ID.
     */
    public function getLevelById($id)
    {
        return Level::query()->find($id);
    }

    /**
     * Получить все уровни.
     */
    public function getAllLevels()
    {
        return Level::all();
    }
}
