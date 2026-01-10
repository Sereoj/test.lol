<?php

namespace App\Models\Content;

use App\Models\Posts\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\TagFactory::new();
    }

    protected $fillable = ['name', 'slug', 'meta'];

    protected $casts = [
        'meta' => 'json',
        'name' => 'json',
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag', 'tag_id', 'post_id');
    }
}
