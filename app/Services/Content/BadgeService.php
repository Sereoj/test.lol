<?php

namespace App\Services\Content;

use App\Models\Content\Badge;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class BadgeService
{
    public function getAll()
    {
        return Badge::all();
    }

    public function getById($id)
    {
        return Badge::find($id);
    }

    public function getActiveBadge($userId)
    {
    }

    public function setActiveBadge($userId, $badgeId)
    {
        return DB::transaction(function () use ($userId, $badgeId) {
            $user = User::query()->find($userId);

            if (!$user) {
                Log::warning('Пользователь не найден', ['user_id' => $userId]);
                throw new ResourceNotFoundException('Пользователь не найден');
            }

            $currentActiveBadge = $user->badges()->wherePivot('is_active', true)->first();

            if ($currentActiveBadge) {
                $user->badges()->updateExistingPivot($currentActiveBadge->id, ['is_active' => false]);
            }

            $userBadge = $user->badges()->where('badge_id', $badgeId)->first();
            if (!$userBadge) {
                Log::warning('Значок не найден у пользователя', [
                    'user_id' => $userId,
                    'badge_id' => $badgeId
                ]);
                throw new ResourceNotFoundException('Значок не найден у пользователя');
            }

            Log::info('Активный значок установлен для пользователя', [
                'user_id' => $userId,
                'badge_id' => $badgeId
            ]);
            return $user->badges()->updateExistingPivot($badgeId, ['is_active' => true]);
        });
    }

    public function create(array $data)
    {
        return Badge::create($data);
    }

    public function update($id, array $data)
    {
        $badge = Badge::find($id);
        if ($badge) {
            $badge->update($data);

            return $badge;
        }

        return null;
    }

    public function delete($id)
    {
        $badge = Badge::find($id);
        return $badge->delete();
    }
}
