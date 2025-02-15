<?php

namespace App\Models\Media;

use App\Models\Posts\Post;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

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
    ];

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
}
