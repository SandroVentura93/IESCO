<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    public const STATUSES = [
        'draft' => 'Borrador',
        'enrolled' => 'Matriculado',
        'cancelled' => 'Anulado',
    ];

    public const GUARDIAN_RELATIONSHIPS = [
        'Padre',
        'Madre',
        'Tutor',
        'Otro',
    ];

    protected $fillable = [
        'student_id',
        'enrollment_code',
        'academic_year',
        'enrollment_date',
        'grade',
        'section',
        'guardian_name',
        'guardian_relationship',
        'guardian_phone',
        'guardian_email',
        'address',
        'notes',
        'status',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'academic_year' => 'integer',
            'enrollment_date' => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
