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
        /**
         * 🔹 Таблица баланса пользователей
         * Каждый пользователь имеет свой баланс и валюту.
         * `balance` — сумма, доступная для покупок/выводов.
         * `pending_balance` — сумма, ожидающая выплату (например, с продаж).
         */
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('balance', 14, 4)->default(0.0000); // Доступные средства
            $table->decimal('pending_balance', 14, 4)->default(0.0000); // Ожидающие выплаты средства
            $table->string('currency', 3)->default('USD')->index(); // Валюта счета
            $table->timestamps();

            //$table->unique('user_id'); // У каждого пользователя только одна запись в этой таблице
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_balances');
    }
};
