<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmploymentStatus\AssignEmploymentStatusRequest;
use App\Http\Requests\EmploymentStatus\RemoveEmploymentStatusRequest;
use App\Services\UserEmploymentStatusService;
use Illuminate\Support\Facades\Auth;

class UserEmploymentStatusController extends Controller
{
    protected UserEmploymentStatusService $userEmploymentStatusService;

    public function __construct(UserEmploymentStatusService $userEmploymentStatusService)
    {
        $this->userEmploymentStatusService = $userEmploymentStatusService;
    }

    public function assignEmploymentStatus(AssignEmploymentStatusRequest $request)
    {
        $user = Auth::user();
        $employmentStatusId = $request->input('employment_status_id');
        $user = $this->userEmploymentStatusService->assignEmploymentStatusToUser($user->id, $employmentStatusId);

        if ($user) {
            return response()->json($user);
        }

        return response()->json(['message' => 'User or EmploymentStatus not found'], 404);
    }

    public function removeEmploymentStatus(RemoveEmploymentStatusRequest $request)
    {
        $user = Auth::user();
        $user = $this->userEmploymentStatusService->removeEmploymentStatusFromUser($user->id);

        if ($user) {
            return response()->json($user);
        }

        return response()->json(['message' => 'User not found'], 404);
    }
}
