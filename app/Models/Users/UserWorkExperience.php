<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWorkExperience extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'user_work_experiences';

    protected $fillable = [
        'user_id',
        'company',
        'position',
        'start_date',
        'end_date',
        'description',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
