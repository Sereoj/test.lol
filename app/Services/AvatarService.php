<?php

namespace App\Services;

use App\Repositories\AvatarRepository;
use Exception;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    private AvatarRepository $avatarRepository;

    public function __construct(AvatarRepository $avatarRepository)
    {
        $this->avatarRepository = $avatarRepository;
    }

    public function uploadAvatar($userId, $file)
    {
        try {
            $path = $file->store('avatars', 'public');

            return $this->avatarRepository->createAvatar([
                'user_id' => $userId,
                'path' => $path,
            ]);
        } catch (Exception $e) {
            throw new Exception('An error occurred while uploading the avatar.');
        }
    }

    public function getUserAvatars($userId)
    {
        try {
            return $this->avatarRepository->getUserAvatars($userId);
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving the user avatars.');
        }
    }

    public function deleteAvatar($userId, $avatarId)
    {
        try {
            $avatar = $this->avatarRepository->findAvatarByUserIdAndId($userId, $avatarId);

            // Удаляем файл аватара из хранилища
            Storage::delete('public/'.$avatar->path);

            return $this->avatarRepository->deleteAvatar($avatar);
        } catch (Exception $e) {
            throw new Exception('An error occurred while deleting the avatar.');
        }
    }
}
