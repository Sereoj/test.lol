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
        Schema::create('challenge_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')
                ->constrained('challenges')
                ->cascadeOnDelete();
            $table->integer('place')
                ->comment('Место (1, 2, 3, и т.д.)');
            $table->decimal('percentage', 5, 2)
                ->comment('Процент от призового фонда (0-100)');
            $table->decimal('amount', 10, 2)
                ->default(0)
                ->comment('Рассчитанная сумма приза (заполняется при создании)');
            $table->timestamps();

            // Уникальность: одно место на один челлендж
            $table->unique(['challenge_id', 'place']);
            $table->index('challenge_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_prizes');
    }
};
