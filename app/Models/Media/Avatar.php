<?php

namespace App\Models\Media;

use App\Models\Users\User;
use App\Services\Media\StorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Avatar",
 *     type="object",
 *     title="Avatar",
 *     description="User avatar model",
 *     @OA\Property(property="id", type="integer", example=1, description="Avatar ID"),
 *     @OA\Property(property="user_id", type="integer", example=1, description="User ID"),
 *     @OA\Property(property="path", type="string", example="avatars/user1.jpg", description="Avatar file path"),
 *     @OA\Property(property="disk", type="string", example="public", description="Storage disk"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Active status"),
 *     @OA\Property(property="url", type="string", example="https://cdn.example.com/avatars/user1.jpg", description="Full URL to avatar"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class Avatar extends Model
{
    use HasFactory;

    protected $table = 'avatars';

    protected $fillable = [
        'user_id',
        'path',
        'disk',
        'is_active'
    ];
    protected $appends = ['url'];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute()
    {
        return StorageService::getPath($this->path);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\AvatarFactory
    {
        return \Database\Factories\AvatarFactory::new();
    }
}
