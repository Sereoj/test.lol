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
        Schema::table('media', function (Blueprint $table) {
            $table->string('source_file_path')->nullable()->after('file_path');
            $table->decimal('source_price', 10, 2)->nullable()->after('source_file_path');
            $table->boolean('has_source')->default(false)->after('source_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['source_file_path', 'source_price', 'has_source']);
        });
    }
};
