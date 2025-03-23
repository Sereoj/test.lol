<?php

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие обновления медиафайла
 */
class MediaUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Медиафайл
     *
     * @var Media
     */
    public Media $media;

    /**
     * Старые данные медиафайла
     *
     * @var array
     */
    public array $oldData;

    /**
     * Создать новый экземпляр события
     *
     * @param Media $media
     * @param array $oldData
     */
    public function __construct(Media $media, array $oldData)
    {
        $this->media = $media;
        $this->oldData = $oldData;
    }
} 