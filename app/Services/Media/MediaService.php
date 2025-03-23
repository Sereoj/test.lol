<?php

namespace App\Services\Media;

use App\Events\Media\MediaCreated;
use App\Events\Media\MediaDeleted;
use App\Events\Media\MediaUpdated;
use App\Handlers\MediaHandler;
use App\Helpers\FileHelper;
use App\Models\Media;
use App\Repositories\Media\MediaRepository;
use App\Services\RepositoryBasedService;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use function App\Helpers\getvideosize;

/**
 * Сервис для работы с медиафайлами
 */
class MediaService extends RepositoryBasedService
{
    /**
     * Обработчик медиафайлов
     *
     * @var MediaHandler
     */
    protected MediaHandler $mediaHandler;

    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'media';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     *
     * @param MediaRepository $repository
     * @param MediaHandler $mediaHandler
     */
    public function __construct(MediaRepository $repository, MediaHandler $mediaHandler)
    {
        parent::__construct($repository);
        $this->mediaHandler = $mediaHandler;
        $this->setLogPrefix('MediaService');
    }

    /**
     * Загрузить файлы
     *
     * @param array $files Файлы для загрузки
     * @param array $options Дополнительные опции
     * @return array
     * @throws Exception
     */
    public function upload($files, array $options): array
    {
        $this->logInfo('Начало загрузки файлов', ['count' => count($files)]);
        
        $originalPath = 'originals';
        $processedPath = 'processed';

        $allCreatedFiles = [];

        if (empty($files) || !is_array($files)) {
            $this->logError('Не предоставлены файлы или неверный формат ввода');
            throw new Exception('Не предоставлены файлы или неверный формат ввода');
        }

        $files = array_filter($files, function ($file) {
            return $file->isValid() && $file->getMimeType();
        });

        foreach ($files as $file) {
            try {
                $allCreatedFiles = array_merge(
                    $allCreatedFiles, 
                    $this->processFile($file, $options, $originalPath, $processedPath)
                );
            } catch (Exception $e) {
                $this->logError("Ошибка обработки файла {$file->getClientOriginalName()}", [
                    'error' => $e->getMessage()
                ], $e);
            }
        }

        $this->logInfo('Завершение загрузки файлов', ['count' => count($allCreatedFiles)]);
        return $allCreatedFiles;
    }

    /**
     * Обработать один файл
     *
     * @param UploadedFile $file Файл для обработки
     * @param array $options Дополнительные опции
     * @param string $originalPath Путь для оригиналов
     * @param string $processedPath Путь для обработанных файлов
     * @return array
     */
    protected function processFile(UploadedFile $file, array $options, string $originalPath, string $processedPath): array
    {
        $mimeType = $file->getMimeType();
        $fileName = $file->getClientOriginalName();
        
        $cacheKey = $this->buildCacheKey('file', [Auth::id(), md5($fileName)]);
        
        return $this->getFromCacheOrStore($cacheKey, 60, function () use ($file, $options, $originalPath, $processedPath, $mimeType, $fileName) {
            $this->logInfo("Обработка файла", ['name' => $fileName, 'type' => $mimeType]);
            
            $type = FileHelper::determineFileType($mimeType);
            if (!$type) {
                $this->logWarning("Неподдерживаемый тип файла", ['mime' => $mimeType]);
                return [];
            }

            $results = $this->mediaHandler->handleFile($type, $file, $options, $originalPath, $processedPath);

            if (empty($results)) {
                $this->logWarning("Не удалось обработать файл", ['name' => $fileName]);
                return [];
            }

            $dimensions = $this->getFileDimensions($file, $type);
            
            return $this->transaction(function () use ($results, $file, $mimeType, $options, $dimensions) {
                $originalMedia = null;
                $mediaData = [];

                foreach ($results as $resultType => $path) {
                    $media = $this->getRepository()->create([
                        'uuid' => Str::uuid(),
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'type' => $resultType,
                        'mime_type' => $mimeType,
                        'size' => $file->getSize(),
                        'user_id' => Auth::id(),
                        'is_public' => $options['is_public'],
                        'width' => $dimensions['width'],
                        'height' => $dimensions['height'],
                        'parent_id' => ($resultType === 'original') ? null : $originalMedia->id,
                    ]);

                    if ($resultType === 'original') {
                        $originalMedia = $media;
                        event(new MediaCreated($media));
                    }

                    $mediaData[] = $media->toArray();
                }

                return $mediaData;
            });
        });
    }

    /**
     * Получить размеры файла
     *
     * @param UploadedFile $file Файл
     * @param string $type Тип файла
     * @return array
     */
    protected function getFileDimensions(UploadedFile $file, string $type): array
    {
        $width = null;
        $height = null;

        switch ($type) {
            case 'image':
                try {
                    [$width, $height] = getimagesize($file);
                } catch (\Exception $e) {
                    $this->logWarning("Не удалось получить размеры изображения", [
                        'name' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
                break;
            case 'video':
                try {
                    [$width, $height] = getvideosize($file);
                } catch (\Exception $e) {
                    $this->logWarning("Не удалось получить размеры видео", [
                        'name' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ]);
                }
                break;
        }

        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * Получить медиафайл по ID
     *
     * @param int $id ID медиафайла
     * @return Media|null
     */
    public function getMediaById(int $id): ?Media
    {
        $cacheKey = $this->buildCacheKey('media', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение медиафайла", ['id' => $id]);
            return $this->getRepository()->getById($id);
        });
    }

    /**
     * Обновить медиафайл
     *
     * @param int $id ID медиафайла
     * @param array $data Данные для обновления
     * @return Media|null
     */
    public function updateMedia(int $id, array $data): ?Media
    {
        $media = $this->getMediaById($id);
        
        if (!$media) {
            $this->logWarning("Медиафайл не найден при попытке обновления", ['id' => $id]);
            return null;
        }
        
        $this->logInfo("Обновление медиафайла", ['id' => $id]);
        
        $oldData = $media->toArray();
        
        $result = $this->transaction(function () use ($id, $data, $media, $oldData) {
            $updatedMedia = $this->getRepository()->update($id, $data);
            
            if ($updatedMedia) {
                $this->forgetCache($this->buildCacheKey('media', [$id]));
                event(new MediaUpdated($updatedMedia, $oldData));
            }
            
            return $updatedMedia;
        });
        
        return $result;
    }

    /**
     * Удалить медиафайл
     *
     * @param int $id ID медиафайла
     * @return bool
     */
    public function deleteMedia(int $id): bool
    {
        $media = $this->getMediaById($id);
        
        if (!$media) {
            $this->logWarning("Медиафайл не найден при попытке удаления", ['id' => $id]);
            return false;
        }
        
        $this->logInfo("Удаление медиафайла", ['id' => $id]);
        
        $mediaCopy = clone $media;
        
        $result = $this->transaction(function () use ($id, $mediaCopy) {
            $deleted = $this->getRepository()->delete($id);
            
            if ($deleted) {
                $this->forgetCache($this->buildCacheKey('media', [$id]));
                event(new MediaDeleted($mediaCopy));
            }
            
            return $deleted;
        });
        
        return $result;
    }

    /**
     * Получить класс события для создания
     *
     * @return string
     */
    protected function getCreateEventClass(): string
    {
        return MediaCreated::class;
    }

    /**
     * Получить класс события для обновления
     *
     * @return string
     */
    protected function getUpdateEventClass(): string
    {
        return MediaUpdated::class;
    }

    /**
     * Получить класс события для удаления
     *
     * @return string
     */
    protected function getDeleteEventClass(): string
    {
        return MediaDeleted::class;
    }
}
