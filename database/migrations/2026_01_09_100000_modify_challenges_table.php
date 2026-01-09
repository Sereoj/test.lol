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
        Schema::table('challenges', function (Blueprint $table) {
            // Организатор челленджа
            $table->foreignId('organizer_id')
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Тип челленджа
            $table->enum('type', ['user', 'official'])
                ->default('user')
                ->after('organizer_id')
                ->comment('user - от пользователя, official - от сайта (verification=true)');

            // Метод определения победителя
            $table->enum('winner_selection_method', ['manual', 'voting_public', 'voting_participants'])
                ->default('manual')
                ->after('type')
                ->comment('manual - организатор выбирает, voting_public - все пользователи, voting_participants - только участники');

            // Комиссия платформы (процент)
            $table->decimal('platform_fee_percentage', 5, 2)
                ->after('prize_currency')
                ->default(0)
                ->comment('Процент комиссии платформы');

            // Сумма комиссии (рассчитывается при создании)
            $table->decimal('platform_fee_amount', 10, 2)
                ->after('platform_fee_percentage')
                ->default(0)
                ->comment('Фактическая сумма комиссии');

            // Чистая сумма приза (после вычета комиссии)
            $table->decimal('net_prize_amount', 10, 2)
                ->after('platform_fee_amount')
                ->default(0)
                ->comment('Сумма приза после вычета комиссии');

            // Счетчики
            $table->integer('submissions_count')
                ->default(0)
                ->after('participants_count')
                ->comment('Количество поданных работ');

            $table->integer('votes_count')
                ->default(0)
                ->after('submissions_count')
                ->comment('Общее количество голосов');

            // Дата завершения голосования
            $table->timestamp('voting_end_date')
                ->nullable()
                ->after('end_date')
                ->comment('Дата окончания голосования');

            // Дата объявления результатов
            $table->timestamp('results_announced_at')
                ->nullable()
                ->after('voting_end_date')
                ->comment('Дата объявления победителей');
        });

        // Изменить валюту по умолчанию с USD на RUB
        DB::statement("ALTER TABLE challenges MODIFY prize_currency VARCHAR(5) DEFAULT 'RUB'");

        // Удалить старый столбец status
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Добавить новый столбец status с расширенными значениями
        Schema::table('challenges', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending_payment',
                'active',
                'voting',
                'selecting_winners',
                'completed',
                'cancelled'
            ])
            ->default('draft')
            ->after('results_announced_at')
            ->comment('draft - черновик, pending_payment - ожидает оплаты, active - активен, voting - голосование, selecting_winners - выбор победителей, completed - завершен, cancelled - отменен');

            // Индексы для оптимизации запросов
            $table->index(['organizer_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['winner_selection_method', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex(['organizer_id', 'status']);
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['winner_selection_method', 'status']);
            $table->dropIndex(['start_date', 'end_date']);

            $table->dropColumn([
                'organizer_id',
                'type',
                'winner_selection_method',
                'platform_fee_percentage',
                'platform_fee_amount',
                'net_prize_amount',
                'submissions_count',
                'votes_count',
                'voting_end_date',
                'results_announced_at',
            ]);
        });

        // Восстановить старый статус
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])
                ->default('draft');
        });

        // Восстановить валюту по умолчанию USD
        DB::statement("ALTER TABLE challenges MODIFY prize_currency VARCHAR(5) DEFAULT 'USD'");
    }
};
