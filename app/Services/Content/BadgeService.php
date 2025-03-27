<?php

namespace App\Services\Content;

use App\Models\Content\Badge;

class BadgeService
{
    public function getAllBadges()
    {
        return Badge::all();
    }

    public function getBadgeById($id)
    {
        return Badge::find($id);
    }

    public function createBadge(array $data)
    {
        return Badge::create($data);
    }

    public function updateBadge($id, array $data)
    {
        $badge = Badge::find($id);
        if ($badge) {
            $badge->update($data);

            return $badge;
        }

        return null;
    }

    public function deleteBadge($id)
    {
        $badge = Badge::find($id);
        return $badge->delete();
    }
}
