<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Services\Media\StorageService;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Log;

class UserCoverService
{
    protected UserService $userService;
    protected string $path = 'users/covers/';
    protected string $disk;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->disk = StorageService::get();
    }

    /**
     * Загрузить и сохранить обложку для пользователя
     *
     * @param int $userId
     * @param UploadedFile $coverFile
     * @return User
     * @throws Exception
     */
    public function uploadCover(int $userId, UploadedFile $coverFile): User
    {
        // Получаем пользователя
        $user = $this->userService->getById($userId);
        if (!$user) {
            throw new Exception('Пользователь не найден');
        }

        $this->removeOldCover($user);

        $filename = $this->generateCoverFilename($coverFile);

        $isUploaded = Storage::disk($this->disk)->put($this->path . $filename, $coverFile);

        if (!$isUploaded) {
            throw new Exception('Обложка не загружена');
        }

        $filePath = $this->path . $filename;

        $user->cover = $filePath;
        $user->save();

        return $user;
    }

    /**
     * Удалить обложку пользователя
     *
     * @param int $userId
     * @return User
     * @throws Exception
     */
    public function removeCover(int $userId): User
    {
        // Получаем пользователя
        $user = $this->userService->getById($userId);
        if (!$user) {
            throw new Exception('Пользователь не найден');
        }

        // Удаляем обложку
        $this->removeOldCover($user);

        // Очищаем поле обложки
        $user->cover = null;
        $user->save();

        return $user;
    }

    /**
     * Удалить старую обложку пользователя из хранилища
     *
     * @param User $user
     * @return void
     */
    private function removeOldCover(User $user): void
    {
        if ($user->cover) {
            $path = $user->cover;
            
            Log::info('Cover path:', [
                'path' => $path
            ]);

            if (Storage::disk($this->disk)->exists($path)) {
                Storage::disk($this->disk)->delete($path);
            }
        }
    }

    /**
     * Генерировать уникальное имя файла для обложки
     *
     * @param UploadedFile $file
     * @return string
     */
    private function generateCoverFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return 'cover_' . Str::uuid() . '.' . $extension;
    }
}
