<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';

    const STATUS_PUBLISHED = 'published';

    const STATUS_ARCHIVED = 'archived';

    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'slug',
        'user_id',
        'content',
        'status',
        'is_adult_content',
        'is_nsfl_content',
        'has_copyright',
        'price',
        'is_free',
        'category_id',
        'settings',
        'meta',
    ];

    protected $casts = [
        'is_adult_content' => 'boolean',
        'is_nsfl_content' => 'boolean',
        'has_copyright' => 'boolean',
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'settings' => 'json',
    ];

    public function statistics()
    {
        return $this->hasOne(PostStatistic::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function apps()
    {
        return $this->belongsToMany(App::class, 'post_app');
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'post_media')
            ->withPivot('sort_order')
            ->orderBy('sort_order')
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDrafts($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}
