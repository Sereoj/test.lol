<?php

namespace App\Services\Posts;

use App\Events\Posts\PostCreated;
use App\Events\Posts\PostDeleted;
use App\Events\Posts\PostUpdated;
use App\Models\Posts\Post;
use App\Repositories\Criteria\Posts\ActivePostsCriteria;
use App\Repositories\Posts\PostRepository;
use App\Services\RepositoryBasedService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис для работы с постами
 */
class PostService extends RepositoryBasedService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'posts';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 30;

    /**
     * Правила валидации при создании
     *
     * @var array
     */
    protected array $createRules = [
        'content' => 'required|string|max:1000',
        'media.*' => 'sometimes|file|max:10240|mimes:jpeg,png,jpg,gif,mp4,avi',
        'status' => 'sometimes|string|in:active,draft',
    ];

    /**
     * Правила валидации при обновлении
     *
     * @var array
     */
    protected array $updateRules = [
        'content' => 'sometimes|string|max:1000',
        'media.*' => 'sometimes|file|max:10240|mimes:jpeg,png,jpg,gif,mp4,avi',
        'status' => 'sometimes|string|in:active,draft',
    ];

    /**
     * Сервис статистики постов
     *
     * @var PostStatisticsService
     */
    protected PostStatisticsService $statisticsService;

    /**
     * Конструктор
     *
     * @param PostRepository $repository
     * @param PostStatisticsService $statisticsService
     */
    public function __construct(PostRepository $repository, PostStatisticsService $statisticsService)
    {
        parent::__construct($repository);
        $this->statisticsService = $statisticsService;
        $this->setLogPrefix('PostService');
    }

    /**
     * Получить список постов с пагинацией
     *
     * @param array $filters
     * @param array $relations
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPosts(array $filters = [], array $relations = ['user'], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = $this->buildCacheKey('posts_list', [$filters, $relations, $perPage]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($filters, $relations, $perPage) {
            $this->logInfo('Получение списка постов с фильтрами', $filters);

            $criteria = [];

            if (isset($filters['status'])) {
                $criteria['status'] = $filters['status'];
            } else {
                $this->withCriteria(new ActivePostsCriteria());
            }

            if (isset($filters['user_id'])) {
                $criteria['user_id'] = $filters['user_id'];
            }

            $posts = $this->getRepository()->getPosts($criteria, ['*'], $relations, $perPage);

            $this->logInfo('Получено постов: ' . $posts->count());

            return $posts;
        });
    }

    /**
     * Получить пост по ID
     *
     * @param int $id
     * @param array $relations
     * @return Post|null
     */
    public function getPost(int $id, array $relations = ['user', 'media']): ?Post
    {
        $cacheKey = $this->buildCacheKey('post', [$id, $relations]);

        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id, $relations) {
            $this->logInfo("Получение поста с ID: {$id}");

            $post = $this->getRepository()->find($id, ['*'], $relations);

            if ($post) {
                $post->increment('views_count');
                $this->forgetCache($cacheKey);
            }

        return $post;
        });
    }

    /**
     * Создать новый пост
     *
     * @param array $data
     * @return Post|null
     */
    public function createPost(array $data): ?Post
    {
        $post = null;

        $this->transaction(function () use ($data, &$post) {
            $this->logInfo('Создание нового поста', $this->maskSensitiveData($data));

            $this->validate($data, $this->createRules);

            $user = Auth::user();
            $data['user_id'] = $user->id;
            $data['status'] = $data['status'] ?? 'active';

            // Обработка медиафайлов
            $mediaFiles = [];
            if (isset($data['media']) && is_array($data['media'])) {
                foreach ($data['media'] as $media) {
                    if ($media instanceof UploadedFile) {
                        $path = $this->storeMedia($media);
                        if ($path) {
                            $mediaFiles[] = ['path' => $path, 'type' => $media->getMimeType()];
                        }
                    }
                }
            }

            // Удаляем из данных поля для медиа
            unset($data['media']);

            $post = $this->getRepository()->create($data);

            // Сохраняем связанные медиафайлы
            if (!empty($mediaFiles) && $post) {
                foreach ($mediaFiles as $file) {
                    $post->media()->create($file);
                }
            }

            if ($post) {
                $this->clearPostCache();

                event(new PostCreated($post));

                $this->logInfo("Пост успешно создан с ID: {$post->id}");
            }
        });

        return $post ?? null;
    }

    /**
     * Обновить пост
     *
     * @param int $id
     * @param array $data
     * @return Post|null
     */
    public function updatePost(int $id, array $data): ?Post
    {
        $post = $this->getRepository()->find($id);

        if (!$post) {
            $this->logWarning("Пост с ID: {$id} не найден при попытке обновления");
            return null;
        }

        if (!$this->canUpdate($post)) {
            $this->logWarning("Отказано в доступе при попытке обновления поста ID: {$id}");
            return null;
        }

        $oldData = $post->toArray();

        $this->transaction(function () use ($post, $data, $oldData, &$updated) {
            $this->logInfo("Обновление поста ID: {$post->id}", $this->maskSensitiveData($data));

            $this->validate($data, $this->updateRules);

            // Обработка медиафайлов
            $mediaFiles = [];
            if (isset($data['media']) && is_array($data['media'])) {
                foreach ($data['media'] as $media) {
                    if ($media instanceof UploadedFile) {
                        $path = $this->storeMedia($media);
                        if ($path) {
                            $mediaFiles[] = ['path' => $path, 'type' => $media->getMimeType()];
                        }
                    }
                }
            }

            // Удаляем из данных поля для медиа
            unset($data['media']);

            $updated = $this->getRepository()->update($data, $post->id);

            // Сохраняем связанные медиафайлы
            if (!empty($mediaFiles) && $updated) {
                foreach ($mediaFiles as $file) {
                    $updated->media()->create($file);
                }
            }

            if ($updated) {
                $this->clearPostCache($post->id);

                event(new PostUpdated($updated, $oldData));

                $this->logInfo("Пост ID: {$post->id} успешно обновлен");
            }
        });

        return null;
    }

    /**
     * Удалить пост
     *
     * @param int $id
     * @return bool
     *
     * @throws \Exception
     */
    public function deletePost(int $id): bool
    {
        $post = $this->getRepository()->find($id);

        if (!$post) {
            $this->logWarning("Пост с ID: {$id} не найден при попытке удаления");
            return false;
        }

        if (!$this->canDelete($post)) {
            $this->logWarning("Отказано в доступе при попытке удаления поста ID: {$id}");
            return false;
        }

        $result = false;

        $this->transaction(function () use ($post, &$result) {
            $this->logInfo("Удаление поста ID: {$post->id}");

            event(new PostDeleted($post));

            // Удаление медиафайлов
            foreach ($post->media as $media) {
                Storage::delete('public/' . $media->path);
                $media->delete();
            }

            $result = $this->getRepository()->delete($post->id);

            if ($result) {
                $this->clearPostCache($post->id);

                $this->logInfo("Пост ID: {$post->id} успешно удален");
            }
        });

        return $result;
    }

    /**
     * Проверяет авторизован ли пользователь и логирует попытку действия
     *
     * @param int $postId
     * @param string $action Описание действия для логирования
     * @return \App\Models\Users\User|\Illuminate\Contracts\Auth\Authenticatable|null Возвращает пользователя или null если не авторизован
     */
    private function checkUserAuthorized(int $postId, string $action): \App\Models\Users\User|\Illuminate\Contracts\Auth\Authenticatable|null
    {
        $user = Auth::user();

        if (!$user) {
            $this->logWarning("Попытка {$action} пост ID: {$postId} без авторизации");
            return null;
        }

        $this->logInfo("Пользователь ID: {$user->id} {$action} пост ID: {$postId}");
        return $user;
    }

    /**
     * Поставить лайк посту
     *
     * @param int $postId
     * @return bool
     */
    public function likePost(int $postId): bool
    {
        $user = $this->checkUserAuthorized($postId, 'лайкает');
        if (!$user) {
            return false;
        }

        $result = $this->getRepository()->likePost($postId, $user->id);

        if ($result) {
            $this->clearPostCache($postId);
        }

        return $result;
    }

    /**
     * Убрать лайк с поста
     *
     * @param int $postId
     * @return bool
     */
    public function unlikePost(int $postId): bool
    {
        $user = $this->checkUserAuthorized($postId, 'удаляет лайк с');
        if (!$user) {
            return false;
        }

        $result = $this->getRepository()->unlikePost($postId, $user->id);

        if ($result) {
            $this->clearPostCache($postId);
        }

        return $result;
    }

    /**
     * Репостнуть пост
     *
     * @param int $postId
     * @param string|null $comment
     * @return Post|null
     */
    public function repostPost(int $postId, ?string $comment = null): ?Post
    {
        $user = $this->checkUserAuthorized($postId, 'репостит');
        if (!$user) {
            return null;
        }

        $repost = $this->getRepository()->repostPost($postId, $user->id, $comment);

        if ($repost) {
            $this->clearPostCache($postId);

            event(new PostCreated($repost));
        }

        return $repost;
    }

    /**
     * Скачать медиафайлы поста
     *
     * @param int $postId
     * @param int|null $mediaId
     * @return StreamedResponse|null
     */
    public function download(int $postId, ?int $mediaId = null): ?StreamedResponse
    {
        $post = $this->getRepository()->find($postId);

        if (!$post) {
            $this->logWarning("Пост с ID: {$postId} не найден при попытке скачивания медиафайлов");
            return null;
        }

        $this->logInfo("Запрос на скачивание медиа для поста ID: {$postId}" . ($mediaId ? ", медиа ID: {$mediaId}" : ''));

        // Если запрошен конкретный медиафайл
        if ($mediaId) {
            $media = $post->media()->find($mediaId);

            if (!$media) {
                $this->logWarning("Медиафайл ID: {$mediaId} не найден для поста ID: {$postId}");
                return null;
            }

            $path = storage_path('app/public/' . $media->path);

            if (!file_exists($path)) {
                $this->logError("Файл не найден на диске: {$path}");
                return null;
            }

            $fileName = basename($media->path);

            $this->logInfo("Скачивание файла: {$fileName}");

            return Storage::disk('public')->download($media->path, $fileName);
        }

        // Если запрошены все медиафайлы (создаем zip-архив)
        $media = $post->media;

        if ($media->isEmpty()) {
            $this->logWarning("У поста ID: {$postId} нет медиафайлов для скачивания");
            return null;
        }

        $zipFileName = "post_{$postId}_media.zip";
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Создаем временную директорию, если она не существует
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Создаем zip-архив
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->logError("Не удалось создать zip-архив: {$zipPath}");
            return null;
        }

        $fileCount = 0;

        foreach ($media as $mediaItem) {
            $filePath = storage_path('app/public/' . $mediaItem->path);

            if (file_exists($filePath)) {
                $fileName = basename($mediaItem->path);
                $zip->addFile($filePath, $fileName);
                $fileCount++;
            } else {
                $this->logWarning("Файл не найден на диске: {$filePath}");
            }
        }

        // Закрываем архив только если он был успешно создан
        if ($zip->status === ZipArchive::ER_OK) {
            $zip->close();
        } else {
            $this->logError("Ошибка при создании архива: {$zip->status}");
            return null;
        }

        if ($fileCount === 0) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            $this->logWarning("Нет доступных файлов для добавления в архив");
            return null;
        }

        $this->logInfo("Создан архив с {$fileCount} файлами: {$zipFileName}");

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Очищает кеш поста и списка постов
     *
     * @param int|null $postId ID поста или null, если нужно очистить только общий кеш
     * @return void
     */
    private function clearPostCache(?int $postId = null): void
    {
        $cacheKeys = [$this->buildCacheKey('posts_list')];

        if ($postId) {
            $cacheKeys[] = $this->buildCacheKey('post', [$postId]);
        }

        $this->forgetCache($cacheKeys);
    }

    /**
     * Очистить кеш сервиса
     *
     * @param int $id
     * @return void
     */
    public function clearServiceCache(int $id): void
    {
        $this->logInfo('Очистка кеша сервиса постов');
        $this->flushCacheByTags([$this->cachePrefix]);
    }

    /**
     * Сохранить медиафайл
     *
     * @param UploadedFile $file
     * @return string|null
     */
    protected function storeMedia(UploadedFile $file): ?string
    {
        $this->logInfo("Сохранение медиафайла: {$file->getClientOriginalName()}");

        try {
            return $file->store('posts/media', 'public');
        } catch (\Exception $e) {
            $this->logError("Ошибка при сохранении медиафайла: {$e->getMessage()}", ['exception' => $e]);
            return null;
        }
    }

    /**
     * Проверить, может ли текущий пользователь обновлять пост
     *
     * @param Model $post
     * @return bool
     */
    protected function canUpdate(Model $post): bool
    {
        // Проверка, что модель - это пост
        if (!($post instanceof Post)) {
            return false;
        }

        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Автор поста или администратор может обновлять
        return $user->id === $post->user_id || $user->hasRole('admin');
    }

    /**
     * Проверить, может ли текущий пользователь удалить пост
     *
     * @param Model $post
     * @return bool
     */
    protected function canDelete(Model $post): bool
    {
        // Проверка, что модель - это пост
        if (!($post instanceof Post)) {
            return false;
        }

        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Автор поста или администратор может удалять
        return $user->id === $post->user_id || $user->hasRole('admin');
    }

    /**
     * Получить класс события для создания
     *
     * @return string
     */
    protected function getCreateEventClass(): string
    {
        return PostCreated::class;
    }

    /**
     * Получить класс события для обновления
     *
     * @return string
     */
    protected function getUpdateEventClass(): string
    {
        return PostUpdated::class;
    }

    /**
     * Получить класс события для удаления
     *
     * @return string
     */
    protected function getDeleteEventClass(): string
    {
        return PostDeleted::class;
    }

    /**
     * Выполняет операцию в транзакции с логированием и очисткой кеша
     *
     * @param string $operation Название операции для логирования
     * @param \Closure $callback Колбэк с основной логикой операции
     * @param int|null $postId ID поста для очистки кеша или null
     * @param string|null $eventClass Класс события для запуска после успешной операции
     * @param array $eventParams Дополнительные параметры для события
     * @return mixed Результат выполнения callback
     */
    private function performPostOperation(
        string $operation,
        \Closure $callback,
        ?int $postId = null,
        ?string $eventClass = null,
        array $eventParams = []
    ) {
        $result = null;

        $this->transaction(function () use ($operation, $callback, $postId, $eventClass, $eventParams, &$result) {
            $this->logInfo($operation);

            $result = $callback();

            if ($result) {
                $this->clearPostCache($postId);

                if ($eventClass) {
                    event(new $eventClass(...$eventParams));
                }
            }
        });

        return $result;
    }
}
