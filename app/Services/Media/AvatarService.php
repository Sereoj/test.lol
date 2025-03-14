<?php

namespace App\Services\Media;

use App\Repositories\AvatarRepository;
use Cache;
use Exception;
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
        try {
            $fileHash = md5_file($file->getPathname());
            $cacheKey = "avatar_upload_{$userId}_{$fileHash}";

            //Убираем дубли повторных загрузок файлов, чтобы не засорять сервер.
            if (Cache::has($cacheKey)) {
                \Log::info("Аватар уже загружен: {$fileHash}");

                return Cache::get($cacheKey);
            }

            $fileName = Str::random(15).'.jpg';
            $path = "avatars/{$fileName}";

            $image = Image::read($file)
                ->encode(new JpegEncoder(80));

            Storage::disk('public')->put($path, $image);

            $avatarData = $this->avatarRepository->createAvatar([
                'user_id' => $userId,
                'path' => $path,
            ]);

            Cache::put($cacheKey, $avatarData, now()->addHour());

            return $avatarData;

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
