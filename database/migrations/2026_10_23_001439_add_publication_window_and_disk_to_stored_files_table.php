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
        Schema::table('stored_files', function (Blueprint $table) {
            $table->string('disk')->default('public')->after('path');
            $table->date('available_from')->nullable()->after('published_at');
            $table->date('available_until')->nullable()->after('available_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stored_files', function (Blueprint $table) {
            $table->dropColumn(['disk', 'available_from', 'available_until']);
        });
    }
};
