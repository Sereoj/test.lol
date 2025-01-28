<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;

    protected $table = 'achievements';

    protected $fillable = ['name', 'description', 'points'];

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
    ];

    public $timestamps = true;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements');
    }
}
