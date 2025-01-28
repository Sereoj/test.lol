<?php

namespace App\Http\Controllers;

use App\Http\Requests\Badge\StoreBadgeRequest;
use App\Http\Requests\Badge\UpdateBadgeRequest;
use App\Services\BadgeService;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    public function index()
    {
        $badges = $this->badgeService->getAllBadges();

        return response()->json($badges);
    }

    public function show($id)
    {
        $badge = $this->badgeService->getBadgeById($id);
        if ($badge) {
            return response()->json($badge);
        }

        return response()->json(['message' => 'Badge not found'], 404);
    }

    public function store(StoreBadgeRequest $request)
    {
        $data = $request->validated();
        $badge = $this->badgeService->createBadge($data);

        return response()->json($badge, 201);
    }

    public function update(UpdateBadgeRequest $request, $id)
    {
        $data = $request->validated();
        $badge = $this->badgeService->updateBadge($id, $data);
        if ($badge) {
            return response()->json($badge);
        }

        return response()->json(['message' => 'Badge not found'], 404);
    }

    public function destroy($id)
    {
        $result = $this->badgeService->deleteBadge($id);
        if ($result) {
            return response()->json(['message' => 'Badge deleted successfully']);
        }

        return response()->json(['message' => 'Badge not found'], 404);
    }
}
