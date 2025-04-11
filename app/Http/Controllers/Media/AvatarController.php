<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Avatar\UploadAvatarRequest;
use App\Http\Resources\AvatarResource;
use App\Services\Media\AvatarService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AvatarController extends Controller
{
    protected AvatarService $avatarService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_AVATARS = 'user_avatars_';

    public function __construct(AvatarService $avatarService)
    {
        $this->avatarService = $avatarService;
    }

    /**
     * Upload an avatar for the authenticated user.
     *
     * @param UploadAvatarRequest $request
     * @return JsonResponse
     */
    public function uploadAvatar(UploadAvatarRequest $request)
    {
        try {
            $user = Auth::user();

            $file = $request->file('avatar');
            $avatar = new AvatarResource($this->avatarService->uploadAvatar($user->id, $file));

            $this->forgetCache(self::CACHE_KEY_USER_AVATARS . $user->id);

            Log::info('Avatar uploaded successfully', ['user_id' => $user->id, 'avatar_id' => $avatar->id]);

            return $this->successResponse(['message' => 'Avatar uploaded successfully', 'avatar' => $avatar]);
        } catch (Exception $e) {
            Log::error('Error uploading avatar: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get all avatars for the authenticated user.
     *
     * @return JsonResponse
     */
    public function getUserAvatars()
    {
        try {
            $user = Auth::user();
            $cacheKey = self::CACHE_KEY_USER_AVATARS . $user->id;

            $avatars = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($user) {
                return $this->avatarService->getUserAvatars($user->id);
            });

            Log::info('User avatars retrieved successfully', ['user_id' => $user->id]);

            return $this->successResponse($avatars);
        } catch (Exception $e) {
            Log::error('Error retrieving user avatars: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete an avatar for the authenticated user.
     *
     * @param  int  $avatarId
     * @return JsonResponse
     */
    public function deleteAvatar($avatarId)
    {
        try {
            $user = Auth::user();
            $this->avatarService->deleteAvatar($user->id, $avatarId);

            $this->forgetCache(self::CACHE_KEY_USER_AVATARS . $user->id);

            Log::info('Avatar deleted successfully', ['user_id' => $user->id, 'avatar_id' => $avatarId]);

            return $this->successResponse(['message' => 'Avatar deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting avatar: ' . $e->getMessage(), ['user_id' => Auth::id(), 'avatar_id' => $avatarId]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
