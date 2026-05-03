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
            // ПРИМЕР: Пользователь пытается создать две активные подписки одновременно.
            // Без индекса: обе записи создаются, у пользователя две подписки (баг).
            // С индексом: вторая попытка выдаёт ошибку дубликата (защита).
            // Также ускоряет поиск: WHERE user_id = ? AND status = 'active' AND expires_at > ?
            $table->unique(['user_id', 'status', 'expires_at'], 'unique_active_subscription');

            // Уникальный индекс для idempotency key
            // ПРИМЕР: Пользователь нажимает "Оплатить" дважды из-за задержки интернета.
            // Без индекса: создаются две подписки, списываются деньги дважды.
            // С индексом: вторая попытка видит дубликат и возвращает существующую подписку.
            // Защита от race conditions при повторных запросах.
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
