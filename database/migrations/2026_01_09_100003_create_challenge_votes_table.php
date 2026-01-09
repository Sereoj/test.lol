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
        Schema::create('challenge_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')
                ->constrained('challenges')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Кто голосует');
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete()
                ->comment('За какую работу голосует');
            $table->timestamps();

            // Один пользователь может голосовать только за одну работу в челлендже
            $table->unique(['challenge_id', 'user_id']);

            // Индексы для подсчета голосов
            $table->index(['challenge_id', 'post_id']);
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_votes');
    }
};
