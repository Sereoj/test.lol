<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmploymentStatus\AssignEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\RemoveEmploymentStatusRequest;
use App\Services\Users\UserEmploymentStatusService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserEmploymentStatusController extends Controller
{
    protected UserEmploymentStatusService $userEmploymentStatusService;

    public function __construct(UserEmploymentStatusService $userEmploymentStatusService)
    {
        $this->userEmploymentStatusService = $userEmploymentStatusService;
    }

    /**
     * Назначить трудовой статус пользователю.
     */
    public function assignEmploymentStatus(AssignEmploymentStatusRequest $request)
    {
        $user = Auth::user();
        $employmentStatusId = $request->input('employment_status_id');

        // Назначаем статус
        $user = $this->userEmploymentStatusService->assignEmploymentStatusToUser($user->id, $employmentStatusId);

        if ($user) {
            // Очищаем кеш трудового статуса пользователя
            Cache::forget('user_employment_status_'.$user->id);

            return response()->json($user);
        }

        return response()->json(['message' => 'User or EmploymentStatus not found'], 404);
    }

    /**
     * Удалить трудовой статус пользователя.
     */
    public function removeEmploymentStatus(RemoveEmploymentStatusRequest $request)
    {
        $user = Auth::user();

        // Удаляем статус
        $user = $this->userEmploymentStatusService->removeEmploymentStatusFromUser($user->id);

        if ($user) {
            // Очищаем кеш трудового статуса пользователя
            Cache::forget('user_employment_status_'.$user->id);

            return response()->json($user);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}
