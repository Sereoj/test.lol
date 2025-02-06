<?php

namespace App\Models;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topup extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'fee', 'currency', 'gateway', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
