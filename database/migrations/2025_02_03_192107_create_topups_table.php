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
         * 🔹 Таблица пополнений баланса
         * Сюда записываются пополнения через эквайринги (anypay, enot и т. д.).
         * `fee` — комиссия эквайринга.
         */
        Schema::create('topups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('amount', 14, 4); // Сумма пополнения
            $table->decimal('fee', 14, 4)->default(0.0000); // Комиссия эквайринга
            $table->string('currency', 3)->index(); // Валюта пополнения
            $table->string('gateway', 20)->index(); // Сервис: 'anypay', 'selection', 'enot', 'tinkoff'
            $table->string('status', 20)->default('pending')->index(); // 'pending', 'succeeded', 'failed'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};
