<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // Мультиязычный заголовок {ru: '', en: ''}
            $table->text('content'); // Полное содержимое статьи
            $table->text('excerpt')->nullable(); // Короткое описание для превью
            $table->string('slug')->unique(); // URL-friendly идентификатор
            $table->json('section')->nullable(); // Секция/категория {ru: '', en: ''}
            $table->string('path'); // Путь к статье (/help/getting-started/account)
            $table->text('keywords')->nullable(); // Ключевые слова для поиска (разделенные запятыми)
            $table->boolean('is_published')->default(true); // Опубликована ли статья
            $table->timestamp('published_at')->nullable(); // Дата публикации
            $table->timestamps();

            // Индексы для поиска
            $table->index('slug');
            $table->index('is_published');
            $table->fullText(['content', 'keywords']); // Полнотекстовый индекс для быстрого поиска
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_articles');
    }
};
