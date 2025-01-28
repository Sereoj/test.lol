<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Avatar\UploadAvatarRequest;
use App\Services\AvatarService;
use Exception;
use Illuminate\Support\Facades\Auth;

class AvatarController extends Controller
{
    protected AvatarService $avatarService;

    public function __construct(AvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    /**
     * Upload an avatar for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAvatar(UploadAvatarRequest $request)
    {
        try {
            $user = Auth::user();
            $file = $request->file('avatar');
            $avatar = $this->avatarService->uploadAvatar($user->id, $file);

            return response()->json(['message' => 'Avatar uploaded successfully', 'avatar' => $avatar], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all avatars for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserAvatars()
    {
        try {
            $user = Auth::user();
            $avatars = $this->avatarService->getUserAvatars($user->id);

            return response()->json($avatars, 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteAvatar($avatarId)
    {
        try {
            $user = Auth::user();
            $this->avatarService->deleteAvatar($user->id, $avatarId);

            return response()->json(['message' => 'Avatar deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
