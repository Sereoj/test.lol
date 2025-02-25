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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->json('meta')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->enum('status', ['draft', 'published', 'archived', 'rejected'])->default('draft');
            $table->boolean('is_adult_content')->default(false);
            $table->boolean('is_nsfl_content')->default(false);
            $table->boolean('has_copyright')->default(false);
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_free')->default(true);
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnUpdate()->cascadeOnDelete();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
