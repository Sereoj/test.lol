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
        Schema::table('challenge_user', function (Blueprint $table) {
            // Убираем submission_data - теперь используем posts
            $table->dropColumn('submission_data');

            // Добавляем поле для отслеживания, подана ли работа
            $table->boolean('has_submitted')
                ->default(false)
                ->after('user_id')
                ->comment('Подал ли участник работу');

            // Дата подачи работы
            $table->timestamp('submitted_at')
                ->nullable()
                ->after('has_submitted')
                ->comment('Дата подачи работы');

            // Индекс для фильтрации участников с работами
            $table->index(['challenge_id', 'has_submitted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_user', function (Blueprint $table) {
            $table->dropIndex(['challenge_id', 'has_submitted']);
            $table->dropColumn(['has_submitted', 'submitted_at']);

            // Возвращаем submission_data
            $table->json('submission_data')->nullable()->after('user_id');
        });
    }
};
