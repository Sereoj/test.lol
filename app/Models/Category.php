<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'meta',
        'name',
        'slug',
        'description',
    ];

    protected $casts = [
        'meta' => 'json',
        'name' => 'json',
    ];
}
