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
        // Создание новой таблицы tasks
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Имя на разных языках
            $table->json('description')->nullable(); // Описание на разных языках
            $table->integer('target'); // Цель (например, количество выполнений)
            $table->enum('period', ['week', 'month', 'day', 'year', 'half_year']); // Период (например, "неделя", "месяц")
            $table->string('type');
            $table->integer('experience_reward'); // Награда в опыте
            $table->integer('virtual_balance_reward'); // Награда в виртуальном балансе
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
