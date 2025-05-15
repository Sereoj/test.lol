<?php

namespace App\Models\Media;

use App\Models\Users\User;
use App\Services\Media\StorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    use HasFactory;

    protected $table = 'avatars';

    protected $fillable = [
        'user_id',
        'path',
        'disk',
        'is_active'
    ];
    protected $appends = ['url'];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute()
    {
        return StorageService::getPath($this->path);
    }
}
