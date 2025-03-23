<?php

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие создания медиафайла
 */
class MediaCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Медиафайл
     *
     * @var Media
     */
    public Media $media;

    /**
     * Создать новый экземпляр события
     *
     * @param Media $media
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }
} 