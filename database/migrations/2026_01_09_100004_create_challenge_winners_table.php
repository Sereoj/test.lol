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
        Schema::create('challenge_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')
                ->constrained('challenges')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Победитель');
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete()
                ->comment('Победившая работа');
            $table->integer('place')
                ->comment('Занятое место (1, 2, 3, и т.д.)');
            $table->decimal('prize_amount', 10, 2)
                ->comment('Сумма выигрыша');
            $table->string('prize_currency', 5)
                ->default('RUB');
            $table->enum('payout_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending')
                ->comment('Статус выплаты приза');
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete()
                ->comment('ID транзакции выплаты');
            $table->timestamp('payout_completed_at')
                ->nullable()
                ->comment('Дата выплаты приза');
            $table->timestamps();

            // Уникальность: одно место на один челлендж
            $table->unique(['challenge_id', 'place']);

            // Индексы
            $table->index(['challenge_id', 'payout_status']);
            $table->index('user_id');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_winners');
    }
};
