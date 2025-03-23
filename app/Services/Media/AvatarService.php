<?php

namespace App\Services\Media;

use App\Repositories\Avatar\AvatarRepository;
use App\Services\Base\SimpleService;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

/**
 * Сервис для работы с аватарами пользователей
 */
class AvatarService extends SimpleService
{
    /**
     * Репозиторий аватаров
     *
     * @var AvatarRepository
     */
    private AvatarRepository $avatarRepository;

    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'avatar';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     *
     * @param AvatarRepository $avatarRepository
     */
    public function __construct(AvatarRepository $avatarRepository)
    {
        parent::__construct();
        $this->avatarRepository = $avatarRepository;
        $this->setLogPrefix('AvatarService');
    }

    /**
     * Загрузить аватар пользователя
     *
     * @param int $userId ID пользователя
     * @param UploadedFile $file Файл аватара
     * @return mixed
     * @throws Exception
     */
    public function uploadAvatar(int $userId, UploadedFile $file): mixed
    {
        try {
            $this->logInfo("Начало загрузки аватара", ['user_id' => $userId]);

            $fileHash = md5_file($file->getPathname());
            $cacheKey = $this->buildCacheKey('upload', [$userId, $fileHash]);

            // Убираем дубли повторных загрузок файлов
            return $this->getFromCacheOrStore($cacheKey, 60, function () use ($userId, $file) {
                $fileName = Str::random(15) . '.jpg';
                $path = "avatars/{$fileName}";

                $this->logInfo("Обработка изображения для аватара", ['user_id' => $userId]);

                $image = Image::read($file)
                    ->encode(new JpegEncoder(80));

                $this->logInfo("Сохранение аватара", ['user_id' => $userId, 'path' => $path]);

                Storage::disk('public')->put($path, $image);

                $avatarData = $this->avatarRepository->createAvatar([
                    'user_id' => $userId,
                    'path' => $path,
                ]);

                $this->logInfo("Аватар успешно загружен", [
                    'user_id' => $userId,
                    'avatar_id' => $avatarData->id
                ]);

                return $avatarData;
            });
        } catch (Exception $e) {
            $this->logError("Ошибка при загрузке аватара", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ], $e);

            throw new Exception('Произошла ошибка при загрузке аватара.');
        }
    }

    /**
     * Получить аватары пользователя
     *
     * @param int $userId ID пользователя
     * @return mixed
     * @throws Exception
     */
    public function getUserAvatars(int $userId): mixed
    {
        try {
            $cacheKey = $this->buildCacheKey('list', [$userId]);

            return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($userId) {
                $this->logInfo("Получение аватаров пользователя", ['user_id' => $userId]);
                return $this->avatarRepository->getUserAvatars($userId);
            });
        } catch (Exception $e) {
            $this->logError("Ошибка при получении аватаров пользователя", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ], $e);

            throw new Exception('Произошла ошибка при получении аватаров пользователя.');
        }
    }

    /**
     * Удалить аватар пользователя
     *
     * @param int $userId ID пользователя
     * @param int $avatarId ID аватара
     * @return bool
     * @throws Exception
     */
    public function deleteAvatar(int $userId, int $avatarId): bool
    {
        try {
            $this->logInfo("Начало удаления аватара", [
                'user_id' => $userId,
                'avatar_id' => $avatarId
            ]);

            return $this->transaction(function () use ($userId, $avatarId) {
                $avatar = $this->avatarRepository->findAvatarByUserIdAndId($userId, $avatarId);

                if (!$avatar) {
                    $this->logWarning("Аватар не найден при попытке удаления", [
                        'user_id' => $userId,
                        'avatar_id' => $avatarId
                    ]);
                    return false;
                }

                // Удаляем файл аватара из хранилища
                Storage::delete('public/' . $avatar->path);

                $result = $this->avatarRepository->deleteAvatar($avatar);

                if ($result) {
                    // Сбрасываем кеш списка аватаров пользователя
                    $this->forgetCache($this->buildCacheKey('list', [$userId]));

                    $this->logInfo("Аватар успешно удален", [
                        'user_id' => $userId,
                        'avatar_id' => $avatarId
                    ]);
                }

                return $result;
            });
        } catch (Exception $e) {
            $this->logError("Ошибка при удалении аватара", [
                'user_id' => $userId,
                'avatar_id' => $avatarId,
                'error' => $e->getMessage()
            ], $e);

            throw new Exception('Произошла ошибка при удалении аватара.');
        }
    }
}
