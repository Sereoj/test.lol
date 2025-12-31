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
        Schema::create('user_premium_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('has_no_ads')->default(false)->comment('Отключение рекламы');
            $table->boolean('has_premium_badge')->default(false)->comment('Бейдж Premium');
            $table->integer('upload_limit')->default(20)->comment('Лимит загрузок в месяц');
            $table->integer('max_file_size')->default(50)->comment('Максимальный размер файла в МБ');
            $table->timestamps();

            // Индекс для быстрого поиска по user_id
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_premium_features');
    }
};
