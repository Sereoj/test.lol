<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Notification",
 *     type="object",
 *     title="Notification",
 *     description="Notification model",
 *     @OA\Property(property="id", type="integer", example=1, description="Notification ID"),
 *     @OA\Property(property="type", type="string", example="App\\Notifications\\NewFollower", description="Notification type"),
 *     @OA\Property(property="notifiable_type", type="string", example="App\\Models\\Users\\User", description="Notifiable model type"),
 *     @OA\Property(property="notifiable_id", type="integer", example=1, description="Notifiable model ID"),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         description="Notification data",
 *         @OA\Property(property="message", type="string", example="You have a new follower"),
 *         @OA\Property(property="user_id", type="integer", example=2)
 *     ),
 *     @OA\Property(property="read_at", type="string", format="date-time", nullable=true, description="Read timestamp"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
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
