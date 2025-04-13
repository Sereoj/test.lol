<?php

namespace App\Models;

use App\Models\Media\Media;
use App\Models\Posts\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    use HasFactory;

    /**
     * Таблица, связанная с моделью.
     *
     * @var string
     */
    protected $table = 'post_media';

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'media_id',
        'sort_order',
    ];

    /**
     * Получить пост, связанный с медиафайлом.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Получить медиафайл, связанный с постом.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
} 