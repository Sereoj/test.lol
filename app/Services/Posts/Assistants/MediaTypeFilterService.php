<?php

namespace App\Services\Posts\Assistants;

use App\Services\Base\SimpleService;
use Exception;

/**
 * Сервис для фильтрации постов по типу медиа
 */
class MediaTypeFilterService extends SimpleService
{
    /**
     * Карта типов медиа
     *
     * @var array
     */
    protected array $mediaTypesMap = [
        'images' => ['image/jpeg', 'image/png', 'image/webp'],
        'videos' => ['video/mp4', 'video/webm', 'video/quicktime'],
        'gifs' => ['image/gif'],
    ];
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('MediaTypeFilterService');
    }

    /**
     * Применить фильтрацию по типам медиа
     *
     * @param mixed $query
     * @param array|null $mediaTypes
     * @param string $filterMode
     * @return void
     */
    public function apply($query, ?array $mediaTypes, string $filterMode = 'or')
    {
        try {
            // Проверяем, что $mediaTypes является массивом
            if (!is_array($mediaTypes)) {
                $this->logWarning('Указан некорректный формат типов медиа', [
                    'media_types' => $mediaTypes,
                    'type' => gettype($mediaTypes)
                ]);
                $mediaTypes = [];
            }

            if (empty($mediaTypes) || in_array('all', $mediaTypes, true)) {
                $this->logInfo('Фильтрация по типам медиа не применена', [
                    'reason' => empty($mediaTypes) ? 'empty_array' : 'all_types_selected'
                ]);
                return; // Без фильтрации
            }

            $this->logInfo('Применение фильтрации по типам медиа', [
                'media_types' => $mediaTypes,
                'filter_mode' => $filterMode
            ]);

            if ($filterMode === 'or') {
                $this->applyOrFilter($query, $mediaTypes);
            } elseif ($filterMode === 'and') {
                $this->applyAndFilter($query, $mediaTypes);
            } else {
                $this->logWarning('Неизвестный режим фильтрации, используется режим OR', [
                    'filter_mode' => $filterMode
                ]);
                $this->applyOrFilter($query, $mediaTypes);
            }
        } catch (Exception $e) {
            $this->logError('Ошибка при применении фильтрации по типам медиа', [
                'media_types' => $mediaTypes,
                'filter_mode' => $filterMode
            ], $e);
        }
    }

    /**
     * Применить фильтрацию в режиме OR
     * 
     * @param mixed $query
     * @param array $mediaTypes
     * @return void
     */
    private function applyOrFilter($query, array $mediaTypes)
    {
        try {
            $allowedMimeTypes = [];
            foreach ($mediaTypes as $type) {
                if (isset($this->mediaTypesMap[$type])) {
                    $allowedMimeTypes = array_merge($allowedMimeTypes, $this->mediaTypesMap[$type]);
                } else {
                    $this->logWarning('Неизвестный тип медиа', [
                        'media_type' => $type,
                        'available_types' => array_keys($this->mediaTypesMap)
                    ]);
                }
            }

            if (!empty($allowedMimeTypes)) {
                $query->whereHas('media', function ($query) use ($allowedMimeTypes) {
                    $query->whereIn('mime_type', $allowedMimeTypes);
                });
                
                $this->logInfo('Применен OR-фильтр по типам медиа', [
                    'allowed_mime_types' => $allowedMimeTypes
                ]);
            } else {
                $this->logWarning('Не найдены допустимые типы MIME для фильтрации');
            }
        } catch (Exception $e) {
            $this->logError('Ошибка при применении OR-фильтрации', [
                'media_types' => $mediaTypes
            ], $e);
        }
    }

    /**
     * Применить фильтрацию в режиме AND
     * 
     * @param mixed $query
     * @param array $mediaTypes
     * @return void
     */
    private function applyAndFilter($query, array $mediaTypes)
    {
        try {
            foreach ($mediaTypes as $type) {
                if (isset($this->mediaTypesMap[$type])) {
                    $allowedMimeTypes = $this->mediaTypesMap[$type];
                    
                    $query->whereHas('media', function ($query) use ($allowedMimeTypes) {
                        $query->whereIn('mime_type', $allowedMimeTypes);
                    });
                    
                    $this->logInfo('Применена AND-фильтрация для типа медиа', [
                        'media_type' => $type,
                        'mime_types' => $allowedMimeTypes
                    ]);
                } else {
                    $this->logWarning('Неизвестный тип медиа', [
                        'media_type' => $type,
                        'available_types' => array_keys($this->mediaTypesMap)
                    ]);
                }
            }
        } catch (Exception $e) {
            $this->logError('Ошибка при применении AND-фильтрации', [
                'media_types' => $mediaTypes
            ], $e);
        }
    }
}
