<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_monthly_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('month')->comment('Месяц (1-12)');
            $table->integer('year')->comment('Год (YYYY)');
            $table->integer('uploads_count')->default(0)->comment('Количество загрузок за месяц');
            $table->timestamps();

            // Уникальный индекс для предотвращения дублей
            $table->unique(['user_id', 'month', 'year']);

            // Индекс для быстрого поиска
            $table->index(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_monthly_stats');
    }
};
