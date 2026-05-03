<?php

namespace App\Services\Content;

use App\Models\Content\Skill;
use App\Models\Users\UserSkill;
use Exception;

class SkillService
{
    public function getAllSkills()
    {
        try {
            return Skill::all();
        } catch (Exception $e) {
            throw new Exception('Произошла ошибка при получении навыков.');
        }
    }

        public function store(array $data)
    {
        return Skill::create($data);
    }

    public function getSkillById($id)
    {
        try {
            return Skill::findOrFail($id);
        } catch (Exception $e) {
            throw new Exception('Произошла ошибка при получении навыка.');
        }
    }

    public function update(int $id, array $data)
    {
        $skill = Skill::find($id);
        if (! $skill) {
            throw new Exception('Навык не найден');
        }

        return $skill->update($data);
    }

    public function delete(int $id)
    {
        $skill = Skill::find($id);
        if (! $skill) {
            throw new Exception('Навык не найден');
        }

        return $skill->delete();
    }

    public function addSkillToUser($userId, array $skillIds)
    {
        $addedSkills = [];

        foreach ($skillIds as $skillId) {
            try {
                $skill = Skill::findOrFail($skillId);

                // Проверяем, существует ли уже запись для данного пользователя и навыка
                $existingRecord = UserSkill::query()->where('user_id', $userId)
                    ->where('skill_id', $skillId)
                    ->first();

                if ($existingRecord) {
                    continue;
                }

                $addedSkills[] = UserSkill::create([
                    'user_id' => $userId,
                    'skill_id' => $skill->id,
                ]);
            } catch (Exception $e) {
                throw new Exception('Произошла ошибка при добавлении навыков пользователю.');
            }
        }

        return $addedSkills;
    }

    public function removeSkillFromUser($userId, $skillId)
    {
        try {
            $skillUser = UserSkill::query()
                ->where('user_id', $userId)
                ->where('skill_id', $skillId)
                ->firstOrFail();

            return $skillUser->delete();
        } catch (Exception $e) {
            throw new Exception('Произошла ошибка при удалении навыка у пользователя.');
        }
    }

    public function getUserSkills($userId)
    {
        try {
            return UserSkill::query()->where('user_id', $userId)->get();
        } catch (Exception $e) {
            throw new Exception('Произошла ошибка при получении навыков пользователя.');
        }
    }
}
