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
        Schema::table('comment_reports', function (Blueprint $table) {
            $table->enum('category', [
                'spam',
                'harassment',
                'hate_speech',
                'violence',
                'nsfw',
                'copyright',
                'misinformation',
                'illegal',
                'ai_generated',
                'other'
            ])->default('other')->after('comment_id');
            $table->enum('status', ['pending', 'reviewed', 'approved', 'rejected', 'resolved'])->default('pending')->after('reason');
            $table->timestamp('reviewed_at')->nullable()->after('status');

            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comment_reports', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['status']);
            $table->dropColumn(['category', 'status', 'reviewed_at']);
        });
    }
};
