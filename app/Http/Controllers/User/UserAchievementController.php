<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Services\AchievementService;
use Auth;
use Illuminate\Http\Request;

class UserAchievementController extends Controller
{
    protected AchievementService $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * Получить все достижения пользователя.
     */
    public function index()
    {
        $user = Auth::user();

        return response()->json($user->achievements);
    }

    /**
     * Добавить достижение пользователю.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'achievement_id' => 'required|exists:achievements,id',
        ]);

        $achievement = Achievement::findOrFail($request->achievement_id);

        $this->achievementService->assignAchievementToUser($user, $achievement);

        return response()->json($user->achievements, 201);
    }

    /**
     * Удалить достижение у пользователя.
     */
    public function destroy(Achievement $achievement)
    {
        $user = Auth::user();
        $this->achievementService->removeAchievementFromUser($user, $achievement);

        return response()->json($user->achievements);
    }
}
