<?php

namespace App\Models\Media;

use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        $disk = $this->disk ?? 'ftp';
        $path = config('filesystems.disks.' . $disk . '.url', '');
        switch ($disk) {
            case 'ftp':
                return  $path .'storage/'. $this->file_path;
            case 'local':
                return $path .'/'. $this->file_path;
            default:
                return $path;
        }
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
