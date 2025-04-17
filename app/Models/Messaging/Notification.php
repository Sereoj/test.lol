<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];

    protected $dates = ['read_at'];

    protected $casts = ['data' => 'array'];

    public function notifiable()
    {
        return $this->morphTo();
    }
} 