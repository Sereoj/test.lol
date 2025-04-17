<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = ['user_id', 'conversation_id', 'content', 'read'];

    protected $casts = ['read' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }
} 