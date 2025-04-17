<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends Model
{
    protected $fillable = ['message_id', 'type', 'url', 'thumbnail_url', 'filename', 'mime_type', 'size'];

    protected $casts = ['size' => 'integer'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
} 