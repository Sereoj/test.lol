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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->string('file_path');
            $table->enum('type', ['original', 'resized', 'blur', 'compressed'])->default('original');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('is_public')->default(true);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('media')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
