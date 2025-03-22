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
            throw new Exception('An error occurred while retrieving skills.');
        }
    }

    public function storeSkill(array $data)
    {
        return Skill::create($data);
    }

    public function getSkillById($id)
    {
        try {
            return Skill::findOrFail($id);
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving the skill.');
        }
    }

    public function updateSkill(int $id, array $data)
    {
        $skill = Skill::find($id);
        if (! $skill) {
            throw new Exception('Skill not found');
        }

        return $skill->update($data);
    }

    public function deleteSkill(int $id)
    {
        $skill = Skill::find($id);
        if (! $skill) {
            throw new Exception('Skill not found');
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
                throw new Exception('An error occurred while adding the skills to the user.');
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
            throw new Exception('An error occurred while removing the skill from the user.');
        }
    }

    public function getUserSkills($userId)
    {
        try {
            return UserSkill::query()->where('user_id', $userId)->get();
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving the user skills.');
        }
    }
}
