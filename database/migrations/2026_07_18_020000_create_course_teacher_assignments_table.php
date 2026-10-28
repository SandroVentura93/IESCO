<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('course_teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('grade')->nullable();
            $table->string('section')->nullable();
            $table->timestamps();
            // Use unique for course + teacher to avoid overly long composite index issues on some MySQL setups
            $table->unique(['course_id','user_id'], 'unique_course_teacher');
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_teacher_assignments');
    }
};
