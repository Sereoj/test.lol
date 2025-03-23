<?php

namespace App\Services\Media;

use App\Services\Base\SimpleService;
use FFMpeg\FFMpeg;
use Exception;

/**
 * Сервис для работы с видео
 */
class VideoService extends SimpleService
{
    /**
     * Пути к бинарным файлам FFmpeg для разных ОС
     *
     * @var array
     */
    protected static array $paths = [
        'Windows' => [
            'ffmpeg'  => 'C:\ffmpeg\bin\ffmpeg.exe',
            'ffprobe' => 'C:\ffmpeg\bin\ffprobe.exe'
        ],
        'Linux' => [
            'ffmpeg'  => '/usr/bin/ffmpeg',
            'ffprobe' => '/usr/bin/ffprobe'
        ]
    ];

    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'video';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('VideoService');
    }

    /**
     * Создает и возвращает FFMpeg инстанс
     *
     * @return FFMpeg|null
     */
    public function getFFMpegInstance(): ?FFMpeg
    {
        $os = PHP_OS_FAMILY;

        if (!isset(self::$paths[$os])) {
            $this->logError("ОС не поддерживается", ['os' => $os]);
            return null;
        }

        try {
            return FFMpeg::create([
                'ffmpeg.binaries'  => self::$paths[$os]['ffmpeg'],
                'ffprobe.binaries' => self::$paths[$os]['ffprobe'],
            ]);
        } catch (Exception $e) {
            $this->logError("Ошибка при создании инстанса FFMpeg", [
                'error' => $e->getMessage()
            ], $e);
            return null;
        }
    }

    /**
     * Получить размеры видео
     *
     * @param string $file Путь к файлу
     * @return array
     */
    public function getVideoSize(string $file): array
    {
        $cacheKey = $this->buildCacheKey('dimensions', [md5($file)]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($file) {
            $this->logInfo("Получение размеров видео", ['file' => $file]);
            
            $ffmpeg = $this->getFFMpegInstance();
            if (!$ffmpeg) {
                $this->logWarning("Не удалось создать инстанс FFMpeg");
                return [0, 0];
            }

            try {
                $video = $ffmpeg->open($file);
                $dimensions = $video->getStreams()->videos()->first()->getDimensions();
                
                $result = [$dimensions->getWidth(), $dimensions->getHeight()];
                
                $this->logInfo("Размеры видео получены", [
                    'file' => $file,
                    'width' => $result[0],
                    'height' => $result[1]
                ]);
                
                return $result;
            } catch (Exception $e) {
                $this->logError("Ошибка при получении размеров видео", [
                    'file' => $file,
                    'error' => $e->getMessage()
                ], $e);
                return [0, 0];
            }
        });
    }

    /**
     * Создать превью для видео
     *
     * @param string $videoPath Путь к видео
     * @param string $thumbnailPath Путь для сохранения превью
     * @param int $timeInSeconds Время снимка в секундах
     * @return bool
     */
    public function createThumbnail(string $videoPath, string $thumbnailPath, int $timeInSeconds = 5): bool
    {
        $this->logInfo("Создание превью для видео", [
            'video' => $videoPath,
            'thumbnail' => $thumbnailPath,
            'time' => $timeInSeconds
        ]);
        
        $ffmpeg = $this->getFFMpegInstance();
        if (!$ffmpeg) {
            $this->logWarning("Не удалось создать инстанс FFMpeg");
            return false;
        }

        try {
            $video = $ffmpeg->open($videoPath);
            $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds($timeInSeconds));
            $frame->save($thumbnailPath);
            
            $this->logInfo("Превью для видео успешно создано", [
                'video' => $videoPath,
                'thumbnail' => $thumbnailPath
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logError("Ошибка при создании превью для видео", [
                'video' => $videoPath,
                'thumbnail' => $thumbnailPath,
                'error' => $e->getMessage()
            ], $e);
            return false;
        }
    }
}
