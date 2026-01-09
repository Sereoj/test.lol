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
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('challenge_id')
                ->nullable()
                ->after('category_id')
                ->constrained('challenges')
                ->nullOnDelete()
                ->comment('ID челленджа, если это работа для участия');

            // Индекс для быстрого получения работ по челленджу
            $table->index(['challenge_id', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['challenge_id', 'status', 'created_at']);
            $table->dropForeign(['challenge_id']);
            $table->dropColumn('challenge_id');
        });
    }
};
