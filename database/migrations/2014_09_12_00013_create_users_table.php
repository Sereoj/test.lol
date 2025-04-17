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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->json('seo_meta')->nullable();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('website')->nullable();
            $table->string('cover')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('verification')->default(false);
            $table->integer('experience')->default(0);
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('language')->nullable()->default('en');
            $table->integer('age')->nullable();
            $table->string('password');
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->foreignId('level_id')->nullable()->constrained('levels')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('userSettings_id')->constrained('user_settings')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('usingApps_id')->nullable()->constrained('apps')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('employment_status_id')->nullable()->constrained('employment_statuses')->cascadeOnDelete();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
