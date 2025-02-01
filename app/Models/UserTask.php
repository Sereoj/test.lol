<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTask extends Model
{
    use HasFactory;

    //period_start
    //period_end

    protected $fillable = ['user_id', 'task_id', 'progress', 'completed', 'period_start', 'period_end'];

    protected $casts = [
        'completed' => 'boolean',
        'progress' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
