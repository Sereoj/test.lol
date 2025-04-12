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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->decimal('prize_amount', 10, 2)->default(0);
            $table->string('prize_currency', 5)->default('USD');
            $table->integer('participants_count')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        // Создаем промежуточную таблицу для связи пользователей с челленджами
        Schema::create('challenge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('submission_data')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['challenge_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_user');
        Schema::dropIfExists('challenges');
    }
};
