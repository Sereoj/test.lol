<?php

namespace App\Services\Users;

use App\Models\Users\User;
use Exception;
use Illuminate\Support\Facades\Log;

class UserPersonalizationService
{
    protected UserBadgeService $userBadgeService;
    protected UserEmploymentStatusService $userEmploymentStatusService;
    protected UserStatusService $userStatusService;

    public function __construct(UserBadgeService $userBadgeService,
                                UserEmploymentStatusService $userEmploymentStatusService,
                                UserStatusService $userStatusService
    )
    {
        $this->userBadgeService = $userBadgeService;
        $this->userEmploymentStatusService = $userEmploymentStatusService;
    }

    public function update(User $user, $data)
    {
        try {
            $this->userBadgeService->setActiveBadgeForUser($user->id, $data['badge_id']);
            $this->userEmploymentStatusService->assignEmploymentStatusToUser($user->id, $data['employment_status_id']);
            $this->userStatusService->assignStatus($user,$data['status_id'] );
            return true;
        } catch (Exception $exception)
        {
            Log::error($exception);
            throw new Exception($exception->getMessage());
        }
    }
}
