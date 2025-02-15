<?php

namespace App\Models\Content;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target',
        'period',
        'type',
        'experience_reward',
        'virtual_balance_reward',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
    ];

    public $timestamps = true;

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_tasks')->withPivot('progress', 'completed')->withTimestamps();
    }
}
