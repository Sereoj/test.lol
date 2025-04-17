<?php

namespace App\Services\Media;

use App\Repositories\AvatarRepository;
use Cache;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Str;

class AvatarService
{
    private AvatarRepository $avatarRepository;

    public function __construct(AvatarRepository $avatarRepository)
    {
        $this->avatarRepository = $avatarRepository;
    }

    public function uploadAvatar($userId, $file)
    {
            $fileName = Str::random(15).'.jpg';
            $path = "avatars/{$fileName}";

            $image = Image::read($file)
                ->encode(new JpegEncoder(80));

            Storage::disk('public')->put($path, $image);

            $avatarData = $this->avatarRepository->createAvatar([
                'user_id' => $userId,
                'path' => $path,
            ]);
            return $avatarData;
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

            Log::info("test", [
                'avatar_id' => $avatarId,
                'user_id' => $userId,]
            );

            // Удаляем файл аватара из хранилища
            Storage::delete('public/'.$avatar->path);

            return $this->avatarRepository->deleteAvatar($avatar);
        } catch (Exception $e) {
            Log::error("An error occurred while deleting the user avatar.");
            throw new Exception($e);
        }
    }

    public function setActive()
    {
    }
}
