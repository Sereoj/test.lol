<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSource extends Model
{
    use HasFactory;

    protected $table = 'source_user';

    protected $fillable = [
        'user_id', 'source_id',
    ];
}
