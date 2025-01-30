<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $table = 'employment_statuses';

    protected $casts = [
        'name' => 'json',
    ];

    public $timestamps = true;

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
