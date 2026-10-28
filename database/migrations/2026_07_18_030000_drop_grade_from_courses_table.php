<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasColumn('courses', 'grade')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('grade');
            });
        }
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('grade')->nullable()->after('name');
        });
    }
};
