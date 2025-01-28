<?php

namespace App\Models;

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
        'meta',
        'user_id',
    ];

    protected $casts = [
        'meta' => 'json',
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
