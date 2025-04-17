<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignStatusRequest;
use App\Http\Resources\StatusResource;
use App\Services\Users\UserStatusService;
use Illuminate\Support\Facades\Auth;

class UserStatusController extends Controller
{
    protected UserStatusService $userStatusService;

    public function __construct(UserStatusService $userStatusService)
    {
        $this->userStatusService = $userStatusService;
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $statuses = StatusResource::collection($this->userStatusService->getAll($user));
            return $this->successResponse($statuses);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve statuses: ' . $e->getMessage(), 500);
        }
    }

    public function assign(AssignStatusRequest $request)
    {
        try {
            $user = Auth::user();
            $status = $this->userStatusService->assignStatus($user, $request->input('status_id'));
            return $this->successResponse(
                [
                    'message' => 'Status assigned successfully',
                    'status' => new StatusResource($status)
                ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to assign status: ' . $e->getMessage(), 500);
        }
    }

    public function detach()
    {
        try {
            $user = Auth::user();
            $this->userStatusService->detachStatus($user);
            return $this->successResponse(['message' => 'Status detached successfully']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to detach status: ' . $e->getMessage(), 500);
        }
    }
}
