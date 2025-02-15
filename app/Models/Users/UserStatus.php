<?php

namespace App\Models\Users;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    use HasFactory;

    protected $table = 'statuses';

    protected $fillable = ['name', 'emoji'];

    protected $casts = [
        'name' => 'json',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
