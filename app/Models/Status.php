<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'emoji'];

    protected $casts = [
        'name' => 'json',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
