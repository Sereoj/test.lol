<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillUser extends Model
{
    use HasFactory;

    protected $table = 'skill_user';

    protected $fillable = [
        'user_id', 'skill_id',
    ];
}
