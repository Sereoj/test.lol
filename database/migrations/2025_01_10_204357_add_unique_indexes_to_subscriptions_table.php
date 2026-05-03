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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Уникальный индекс для защиты от дубликатов активных подписок
            $table->unique(['user_id', 'status', 'expires_at'], 'unique_active_subscription');

            // Уникальный индекс для idempotency key
            $table->unique(['user_id', 'idempotency_key'], 'unique_idempotency_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropUnique('unique_active_subscription');
            $table->dropUnique('unique_idempotency_key');
        });
    }
};
