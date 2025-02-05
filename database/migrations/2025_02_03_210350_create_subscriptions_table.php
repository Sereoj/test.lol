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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan'); // Название или тип подписки (например, 'basic', 'premium')
            $table->string('status')->default('active'); // Статус подписки ('active', 'inactive', 'expired')
            $table->decimal('amount', 10, 2); // Сумма подписки
            $table->string('currency', 3); // Валюта подписки (например, 'USD')
            $table->timestamp('started_at'); // Дата начала подписки
            $table->timestamp('expires_at'); // Дата окончания подписки
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
