<?php

namespace App\Models\Users;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    use HasFactory;

    protected $table = 'levels';

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
