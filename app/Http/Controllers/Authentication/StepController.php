<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Media\AvatarController;
use App\Http\Requests\Avatar\UploadAvatarRequest;
use App\Http\Requests\Step\StepOneRequest;
use App\Http\Requests\Step\StepTwoRequest;
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

    public function one(StepOneRequest $request)
    {
        $request->user()->sources()->syncWithoutDetaching($request->get('source_id'));
        return response()->json(['message' => 'Source successfully added']);
    }

    public function two(StepTwoRequest $request)
    {
        $request->user()->skills()->syncWithoutDetaching($request->get('skill_ids'));
        return response()->json(['message' => 'Skills successfully added']);
    }

    public function three(UploadAvatarRequest $request)
    {
        return $this->avatarController->uploadAvatar($request);
    }
}
