<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Media\AvatarController;
use App\Http\Requests\Avatar\UploadAvatarRequest;
use App\Models\Users\User;
use App\Services\Media\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StepController extends Controller
{
    protected AvatarService $avatarService;

    private AvatarController $avatarController;

    public function __construct(AvatarService $avatarService, AvatarController $avatarController)
    {
        $this->avatarService = $avatarService;
        $this->avatarController = $avatarController;
    }

    public function one(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        $request->validate([
            'source_id' => 'required|exists:sources,id',
        ]);

        $user->sources()->syncWithoutDetaching($request->get('source_id'));

        return response()->json(['message' => 'Source successfully added']);
    }

    public function two(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        $request->validate([
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'exists:skills,id',
        ]);

        $user->skills()->syncWithoutDetaching($request->get('skill_ids'));

        return response()->json(['message' => 'Skills successfully added']);
    }

    public function three(UploadAvatarRequest $request)
    {
        $user = $this->getAuthenticatedUser();

        return $this->avatarController->uploadAvatar($request);
    }

    private function getAuthenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'User not authorized');
        }

        return $user;
    }
}
