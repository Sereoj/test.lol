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
         * 🔹 Таблица комиссий
         * Включает комиссии эквайрингов, платформы и вывода.
         * Может быть фиксированная сумма или процент от суммы.
         */
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index(); // 'acquiring', 'platform', 'withdrawal'
            $table->string('gateway')->nullable()->index(); // Только для эквайрингов
            $table->decimal('percentage', 5, 2)->nullable(); // Процент комиссии
            $table->decimal('fixed_amount', 14, 4)->nullable(); // Фиксированная комиссия
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
