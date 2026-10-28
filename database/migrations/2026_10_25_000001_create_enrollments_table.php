<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->string('enrollment_code', 32)->nullable()->unique();
            $table->unsignedSmallInteger('academic_year');
            $table->date('enrollment_date');
            $table->string('grade', 20);
            $table->string('section', 20)->nullable();
            $table->string('guardian_name', 180);
            $table->string('guardian_relationship', 30);
            $table->string('guardian_phone', 40)->nullable();
            $table->string('guardian_email', 180)->nullable();
            $table->string('address', 500)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year']);
            $table->index(['academic_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
