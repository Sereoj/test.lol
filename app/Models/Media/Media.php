<?php

namespace App\Models\Media;

use App\Models\Posts\Post;
use App\Models\Users\User;
use App\Services\Media\StorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Media",
 *     type="object",
 *     title="Media",
 *     description="Media file model",
 *     @OA\Property(property="id", type="integer", example=1, description="Media ID"),
 *     @OA\Property(property="name", type="string", example="image.jpg", description="File name"),
 *     @OA\Property(property="file_path", type="string", example="media/2024/01/image.jpg", description="File path"),
 *     @OA\Property(property="mime_type", type="string", example="image/jpeg", description="MIME type"),
 *     @OA\Property(property="type", type="string", example="original", enum={"original", "resized", "blur", "compressed"}, description="Media type"),
 *     @OA\Property(property="size", type="integer", example=1024000, description="File size in bytes"),
 *     @OA\Property(property="width", type="integer", nullable=true, example=1920, description="Image width"),
 *     @OA\Property(property="height", type="integer", nullable=true, example=1080, description="Image height"),
 *     @OA\Property(property="user_id", type="integer", example=1, description="Owner user ID"),
 *     @OA\Property(property="is_public", type="boolean", example=true, description="Public access flag"),
 *     @OA\Property(property="uuid", type="string", example="550e8400-e29b-41d4-a716-446655440000", description="UUID"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent media ID for variants"),
 *     @OA\Property(property="disk", type="string", example="public", description="Storage disk"),
 *     @OA\Property(property="url", type="string", example="https://cdn.example.com/media/image.jpg", description="Full URL to media file"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class Media extends Model
{
    use HasFactory;

    public const STATUS_ORIGINAL = 'original';
    public const STATUS_RESIZED = 'resized';
    public const STATUS_BLUR = 'blur';
    public const STATUS_COMPRESSED = 'compressed';

    protected $fillable = [
        'name',
        'file_path',
        'mime_type',
        'type',
        'size',
        'user_id',
        'is_public',
        'uuid',
        'width',
        'height',
        'size',
        'parent_id',
        'disk'
    ];

    protected $appends = ['url'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_media')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute()
    {
        // Use the disk from the media record, not the current config
        return StorageService::getPath($this->file_path, $this->disk);
    }

    public function scopeOriginal($query)
    {
        return $query->where('type', self::STATUS_ORIGINAL);
    }

    public function scopeResized($query)
    {
        return $query->where('type', self::STATUS_RESIZED);
    }

    public function scopeBlur($query)
    {
        return $query->where('type', self::STATUS_BLUR);
    }

    public function scopeCompressed($query)
    {
        return $query->where('type', self::STATUS_COMPRESSED);
    }
}
