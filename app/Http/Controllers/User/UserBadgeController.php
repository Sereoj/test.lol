<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserBadgeRequest;
use App\Http\Requests\UpdateUserBadgeRequest;
use App\Services\UserBadgeService;
use Illuminate\Http\Request;

class UserBadgeController extends Controller
{
    protected UserBadgeService $userBadgeService;

    public function __construct(UserBadgeService $userBadgeService)
    {
        $this->userBadgeService = $userBadgeService;
    }

    public function index()
    {
        return $this->userBadgeService->getAllUserBadges();
    }

    public function store(StoreUserBadgeRequest $request)
    {
        return $this->userBadgeService->createUserBadge($request->validated());
    }

    public function show($id)
    {
        return $this->userBadgeService->getUserBadgeById($id);
    }

    public function update(UpdateUserBadgeRequest $request, $id)
    {
        return $this->userBadgeService->updateUserBadge($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->userBadgeService->deleteUserBadge($id);
    }
}
