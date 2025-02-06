<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'balance', 'pending_balance', 'currency'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
