<?php

namespace App\Models\Content;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'description',
        'options',
        'image',
    ];

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'options' => 'json',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badge')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function isAchievementBased()
    {
        return isset($this->options['availability']) && $this->options['availability'] === 'achievement';
    }

    public function isPurchaseBased()
    {
        return isset($this->options['availability']) && $this->options['availability'] === 'purchase';
    }

    public function getRequirements()
    {
        return $this->options['requirements'] ?? null;
    }
}
