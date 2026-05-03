<?php

namespace App\Services\Media;

use App\Models\Users\User;
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
    protected string $directoryName = 'avatars';
    protected string $disk;
    public function __construct(AvatarRepository $avatarRepository)
    {
        $this->disk = StorageService::get();
        if(!Storage::disk($this->disk)->exists($this->directoryName))
        {
            Storage::disk($this->disk)->makeDirectory($this->directoryName);

            Log::info('диск', [
                'disk' => $this->disk,
                'directory' => $this->directoryName,
                'status' => 'created'
            ]);
        }

        $this->avatarRepository = $avatarRepository;
    }

    public function uploadAvatar($userId, $file)
    {
            $fileName = Str::random(15).'.jpg';
            $path = "{$this->directoryName}/{$fileName}";

            $image = Image::read($file)
                ->encode(new JpegEncoder(80));

            $isUploaded = Storage::disk($this->disk)->put($path, $image);

            if (!$isUploaded) {
                throw new Exception('Аватар не загружен');
            }

            $filePath = $path;

        return $this->avatarRepository->createAvatar([
            'user_id' => $userId,
            'disk' => $this->disk,
            'path' => $filePath,
            'is_active' => true
        ]);
    }

    public function getUserAvatars($userId)
    {
        try {
            return $this->avatarRepository->getUserAvatars($userId);
        } catch (Exception $e) {
            throw new Exception('Произошла ошибка при получении аватаров пользователя.');
        }
    }

    public function setActive(User $user, int $avatarId)
    {
        return $this->avatarRepository->setActive(
            $user,
            $avatarId
        );
    }

    public function setAvatar($userId, $path)
    {
        return $this->avatarRepository->createAvatar([
            'user_id' => $userId,
            'path' => $path,
            'disk' => $this->disk,
            'is_active' => true
        ]);
    }

    public function deleteAvatar($userId, $avatarId)
    {
        try {
            $avatar = $this->avatarRepository->findAvatarByUserIdAndId($userId, $avatarId);

            Log::info("тест", [
                'avatar_id' => $avatarId,
                'user_id' => $userId,
                'avatar' => $avatar,
            ]);

            // Удаляем файл аватара из хранилища
            $isDeleted = Storage::disk($this->disk)->delete($avatar->path);

            if (!$isDeleted) {
                throw new Exception('Аватар не удален');
            }

            return $this->avatarRepository->deleteAvatar($avatar);
        } catch (Exception $e) {
            Log::error('Произошла ошибка при удалении аватара пользователя.');
            throw new Exception($e);
        }
    }
}
