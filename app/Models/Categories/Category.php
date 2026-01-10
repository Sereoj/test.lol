<?php

namespace App\Models\Categories;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use HasFactory;
    use SoftDeletes;

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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\CategoryFactory
    {
        return \Database\Factories\CategoryFactory::new();
    }
}
