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

        Log::info('UserCoverService: Starting cover upload', [
            'user_id' => $userId,
            'file_name' => $coverFile->getClientOriginalName(),
            'file_size' => $coverFile->getSize(),
            'mime_type' => $coverFile->getMimeType(),
            'disk' => $this->disk,
        ]);

        $this->removeOldCover($user);

        $filename = $this->generateCoverFilename($coverFile);

        try {
            // Upload file with public visibility and ACL for S3
            $uploadOptions = ['visibility' => 'public'];

            // For S3, add explicit ACL header
            if ($this->disk === 's3') {
                $uploadOptions['ACL'] = 'public-read';
            }

            $filePath = Storage::disk($this->disk)->putFileAs(
                $this->path,
                $coverFile,
                $filename,
                $uploadOptions
            );

            if (!$filePath) {
                throw new Exception('Не удалось загрузить обложку');
            }

            // Get full URL for verification
            $url = StorageService::getPath($filePath, $this->disk);

            Log::info('UserCoverService: Cover uploaded successfully', [
                'user_id' => $userId,
                'path' => $filePath,
                'url' => $url,
                'disk' => $this->disk,
            ]);

            $user->cover = $filePath;
            $user->disk = $this->disk;
            $user->save();

            return $user;

        } catch (\Exception $e) {
            Log::error('UserCoverService: Cover upload failed', [
                'user_id' => $userId,
                'filename' => $filename,
                'disk' => $this->disk,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Ошибка при загрузке обложки: ' . $e->getMessage());
        }
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
            // Используем диск из модели пользователя, если он есть, иначе текущий диск
            $disk = $user->disk ?? $this->disk;

            Log::info('Removing cover', [
                'path' => $path,
                'disk' => $disk
            ]);

            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                    Log::info('Cover deleted successfully', ['path' => $path, 'disk' => $disk]);
                } else {
                    Log::warning('Cover file not found', ['path' => $path, 'disk' => $disk]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete cover', [
                    'path' => $path,
                    'disk' => $disk,
                    'error' => $e->getMessage()
                ]);
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
