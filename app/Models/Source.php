<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'iconUrl',
    ];

    protected $casts = [
        'name' => 'json',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'source_user');
    }
}
