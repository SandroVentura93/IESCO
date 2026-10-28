<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Ensure any stray table from earlier failed attempts is removed before running the real create migration
        Schema::dropIfExists('course_teacher_assignments');
    }

    public function down()
    {
        // no-op: the create migration will recreate the table
    }
};
