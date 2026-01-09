<?php

namespace App\Repositories;

use App\Models\Users\UserWorkExperience;
use Illuminate\Database\Eloquent\Collection;

class WorkExperienceRepository
{
    public function create(array $data): UserWorkExperience
    {
        return UserWorkExperience::query()->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $workExperience = UserWorkExperience::query()->findOrFail($id);
        return $workExperience->update($data);
    }

    public function delete(int $id): bool
    {
        $workExperience = UserWorkExperience::query()->findOrFail($id);
        return $workExperience->delete();
    }

    public function findById(int $id): ?UserWorkExperience
    {
        return UserWorkExperience::query()->find($id);
    }

    public function getByUserId(int $userId): Collection
    {
        return UserWorkExperience::query()
            ->where('user_id', $userId)
            ->orderByDesc('start_date')
            ->get();
    }

    public function getCurrentWorkExperience(int $userId): ?UserWorkExperience
    {
        return UserWorkExperience::query()
            ->where('user_id', $userId)
            ->where('is_current', true)
            ->first();
    }
}
