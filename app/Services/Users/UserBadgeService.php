<?php

namespace App\Services\Users;

use App\Models\Users\UserBadge;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserBadgeService
{
    public function getAllUserBadges()
    {
        return UserBadge::with('badge')->get();
    }

    public function createUserBadge(array $data)
    {
        Auth::user()->badges()->syncWithoutDetaching([$data['badge_id']]);

        return ['message' => 'Badge assigned successfully'];
    }

    public function getUserBadgeById($id)
    {
        $userBadge = UserBadge::query()
            ->where('user_id', Auth::id())
            ->where('badge_id', $id)
            ->first();

        if ($userBadge) {
            return $userBadge;
        }

        return ['message' => 'Badge not found'];
    }

    public function updateUserBadge($id, array $data)
    {
        $userBadge = UserBadge::query()->findOrFail($id);

        $existingBadge = UserBadge::query()->where('user_id', Auth::id())
            ->where('badge_id', $data['badge_id'])
            ->where('id', '<>', $id)
            ->first();

        if ($existingBadge) {
            throw new Exception('Badge already assigned to the user');
        }

        $userBadge->update($data);

        return $userBadge;
    }

    /**
     * Устанавливает значок как активный для указанного пользователя
     *
     * @param int $userId ID пользователя
     * @param int $badgeId ID значка, который нужно сделать активным
     * @return UserBadge|array Активированный значок или сообщение об ошибке
     * @throws Exception Если произошла ошибка при установке активного значка
     */
    public function setActiveBadgeForUser($userId, $badgeId)
    {
        try {
            DB::beginTransaction();

            UserBadge::query()
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $userBadge = UserBadge::query()
                ->where('user_id', $userId)
                ->where('badge_id', $badgeId)
                ->first();

            if (! $userBadge) {
                throw new Exception('Badge not found for the user.');
            }

            $userBadge->update(['is_active' => true]);

            DB::commit();

            return $userBadge;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error setting active badge: '.$e->getMessage(), [
                'user_id' => $userId,
                'badge_id' => $badgeId
            ]);
            throw $e;
        }
    }

    /**
     * Получает активный значок для указанного пользователя
     *
     * @param int $userId ID пользователя
     * @return UserBadge|null Активный значок пользователя или null, если его нет
     */
    public function getActiveBadgeForUser($userId)
    {
        return UserBadge::query()
            ->where('user_id', $userId)
            ->active()
            ->first();
    }

    public function deleteUserBadge($id)
    {
        $userBadge = UserBadge::query()->findOrFail($id);
        $userBadge->delete();

        return ['message' => 'UserBadge deleted successfully'];
    }
}
