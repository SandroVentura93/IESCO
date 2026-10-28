<?php

namespace App\Models;

use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoredFile extends Model
{
    public const MEDIA_LIBRARY_SECTION = 'Biblioteca_Multimedia';

    protected $table = 'stored_files';
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'disk',
        'mime',
        'size',
        'visibility',
        'published_at',
        'available_from',
        'available_until',
        'grade',
        'student_id',
        'course_id',
        'file_type',
        'description',
        'section',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'available_from' => 'date',
        'available_until' => 'date',
    ];

    public function scopePubliclyAvailable(Builder $query): Builder
    {
        $today = today(config('school.timezone', 'America/Lima'))->toDateString();

        return $query
            ->where('visibility', 'published')
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('available_from')->orWhereDate('available_from', '<=', $today);
            })
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('available_until')->orWhereDate('available_until', '>=', $today);
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
