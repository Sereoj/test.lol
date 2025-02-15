<?php

namespace App\Models\Content;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'color',
    ];

    protected $casts = [
        'name' => 'json',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'skill_user');
    }
}
