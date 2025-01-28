<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'experience_required'];

    protected $casts = [
        'experience_required' => 'integer',
        'name' => 'json',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public $timestamps = true;

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
