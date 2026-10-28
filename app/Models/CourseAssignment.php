<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAssignment extends Model
{
    use HasFactory;

    public const GRADES = ['1°', '2°', '3°', '4°', '5°', '6°', 'Otro'];

    protected $table = 'course_teacher_assignments';

    protected $fillable = [
        'course_id',
        'user_id',
        'grade',
        'section',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
