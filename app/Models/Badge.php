<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
        'options',
        'image',
    ];

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'options' => 'json',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badge');
    }
}
