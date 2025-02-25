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
         * 🔹 Таблица всех транзакций (пополнения, покупки, выводы, переводы)
         * Используется для журналирования финансовых операций пользователей.
         * `metadata` — JSON с дополнительными данными (например, ID поста, эквайринг и т. д.).
         */
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type', 20)->index(); // Тип: 'topup', 'purchase', 'withdrawal', 'transfer'
            $table->decimal('amount', 14, 4);
            $table->string('currency', 3)->index();
            $table->string('status', 20)->default('pending')->index(); // 'pending', 'completed', 'failed'
            $table->json('metadata')->nullable(); // JSON с доп. инфо (ID поста, способ оплаты и т. д.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
