<?php

namespace App\Models\Roles;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['name', 'type'];

    protected $casts = [
        'name' => 'json',
        'type' => 'string',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
