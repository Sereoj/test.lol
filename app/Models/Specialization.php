<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type'];

    protected $casts = [
        'name' => 'json',
    ];

    public $timestamps = true;

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_specialization');
    }
}
