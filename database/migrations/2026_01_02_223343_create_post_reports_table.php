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
        Schema::create('post_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnUpdate()->cascadeOnDelete();
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
            ])->default('other');
            $table->text('reason');
            $table->enum('status', ['pending', 'reviewed', 'approved', 'rejected', 'resolved'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'post_id'], 'unique_user_post_report');
            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_reports');
    }
};
