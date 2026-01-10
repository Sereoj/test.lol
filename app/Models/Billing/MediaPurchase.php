<?php

namespace App\Models\Billing;

use App\Models\Media\Media;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaPurchase extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'media_id', 'amount', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\MediaPurchaseFactory
    {
        return \Database\Factories\MediaPurchaseFactory::new();
    }
}
