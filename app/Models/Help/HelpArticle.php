<?php

namespace App\Models\Help;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpArticle extends Model
{
    use HasFactory;

    /**
     * Название таблицы
     *
     * @var string
     */
    protected $table = 'help_articles';

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'slug',
        'section',
        'path',
        'keywords',
        'is_published',
        'published_at',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы в типы.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'array',        // JSON поле для мультиязычного заголовка
        'section' => 'array',      // JSON поле для мультиязычной секции
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope для получения только опубликованных статей.
     *
     * @param $query
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope для поиска по тексту.
     *
     * @param $query
     * @param string $searchTerm
     * @return mixed
     */
    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            // Поиск по title (JSON поле)
            $q->where('title->ru', 'LIKE', "%{$searchTerm}%")
                ->orWhere('title->en', 'LIKE', "%{$searchTerm}%")
                // Поиск по content
                ->orWhere('content', 'LIKE', "%{$searchTerm}%")
                // Поиск по keywords
                ->orWhere('keywords', 'LIKE', "%{$searchTerm}%")
                // Поиск по section (JSON поле)
                ->orWhere('section->ru', 'LIKE', "%{$searchTerm}%")
                ->orWhere('section->en', 'LIKE', "%{$searchTerm}%");
        });
    }
}
