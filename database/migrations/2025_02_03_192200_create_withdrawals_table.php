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
         * 🔹 Таблица запросов на вывод средств
         * Пользователи могут выводить деньги, но выплаты происходят позже.
         * `fee` — комиссия на вывод, которая может настраиваться.
         */
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('amount', 14, 4); // Сумма вывода
            $table->decimal('fee', 14, 4)->default(0.0000); // Комиссия за вывод
            $table->string('currency', 3)->index(); // Валюта вывода
            $table->string('status', 20)->default('pending')->index(); // 'pending', 'completed', 'failed'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
