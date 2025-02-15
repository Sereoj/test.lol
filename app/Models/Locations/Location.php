<?php

namespace App\Models\Locations;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $casts = [
        'name' => 'json',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
