<?php

namespace App\Services\Users;

use App\Models\Users\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserCoverService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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

        // Удаляем старую обложку, если она существует
        $this->removeOldCover($user);

        // Генерируем имя файла и путь
        $filename = $this->generateCoverFilename($coverFile);
        $path = $coverFile->storeAs('public/users/covers', $filename);

        // Сохраняем путь к обложке в модель пользователя
        $user->cover = Storage::url($path);
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
            $oldPath = str_replace('/storage', 'public', $user->cover);
            if (Storage::exists($oldPath)) {
                Storage::delete($oldPath);
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