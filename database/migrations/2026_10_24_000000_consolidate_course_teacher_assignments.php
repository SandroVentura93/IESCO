<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_teacher') || !Schema::hasTable('course_teacher_assignments')) {
            return;
        }

        DB::table('course_teacher')
            ->join('users', 'users.id', '=', 'course_teacher.user_id')
            ->where('users.role', 'docente')
            ->select('course_teacher.course_id', 'course_teacher.user_id', 'course_teacher.created_at', 'course_teacher.updated_at')
            ->orderBy('course_teacher.course_id')
            ->orderBy('course_teacher.user_id')
            ->chunk(500, function ($assignments): void {
                $assignments = $assignments->map(fn (object $assignment): array => [
                    'course_id' => $assignment->course_id,
                    'user_id' => $assignment->user_id,
                    'grade' => null,
                    'section' => null,
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ])->all();

                DB::table('course_teacher_assignments')->insertOrIgnore($assignments);
            });
    }

    public function down(): void
    {
    }
};
