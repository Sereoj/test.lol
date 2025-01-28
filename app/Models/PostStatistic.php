<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'views_count',
        'likes_count',
        'reposts_count',
        'downloads_count',
        'purchases_count',
        'comments_count',
        'impressions_count',
        'clicks_count',
        'shares_count',
        'engagement_score',
        'last_interaction_at',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
