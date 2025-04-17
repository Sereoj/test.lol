<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['creator_id', 'recipient_id', 'last_message_at'];

    protected $dates = ['last_message_at'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class, 'creator_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class, 'recipient_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }
} 