<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('stored_files', function (Blueprint $table) {
            if (!Schema::hasColumn('stored_files', 'grade')) {
                $table->string('grade')->nullable()->after('visibility');
            }
            if (!Schema::hasColumn('stored_files', 'file_type')) {
                $table->string('file_type')->nullable()->after('grade');
            }
        });
    }

    public function down()
    {
        Schema::table('stored_files', function (Blueprint $table) {
            if (Schema::hasColumn('stored_files', 'file_type')) $table->dropColumn('file_type');
            if (Schema::hasColumn('stored_files', 'grade')) $table->dropColumn('grade');
        });
    }
};
