<?php

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие удаления медиафайла
 */
class MediaDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Медиафайл перед удалением
     *
     * @var Media
     */
    public Media $media;

    /**
     * ID медиафайла
     *
     * @var int
     */
    public int $mediaId;

    /**
     * Создать новый экземпляр события
     *
     * @param Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
        $this->mediaId = $media->id;
    }
} 