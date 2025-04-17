<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'notification_settings';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email_enabled',
        'push_enabled',
        'notify_on_new_message',
        'notify_on_new_follower',
        'notify_on_post_like',
        'notify_on_comment',
        'notify_on_comment_like',
        'notify_on_mention',
    ];

    /**
     * Атрибуты, которые должны быть приведены к определенным типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'notify_on_new_message' => 'boolean',
        'notify_on_new_follower' => 'boolean',
        'notify_on_post_like' => 'boolean',
        'notify_on_comment' => 'boolean',
        'notify_on_comment_like' => 'boolean',
        'notify_on_mention' => 'boolean',
    ];

    /**
     * Получить пользователя, которому принадлежат эти настройки уведомлений.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
