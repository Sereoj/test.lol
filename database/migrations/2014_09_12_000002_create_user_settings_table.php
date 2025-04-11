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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_preferences_feed')->default(false);
            $table->enum('preferences_feed', ['popularity', 'downloads', 'likes', 'default'])->default('default');
            $table->boolean('is_private')->default(false);
            $table->boolean('enable_two_factor')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
