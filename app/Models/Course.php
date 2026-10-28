<?php

namespace App\Models;

use App\Models\CourseAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['name'];

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teacher_assignments', 'course_id', 'user_id')
            ->withPivot(['grade', 'section'])
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class, 'course_id');
    }

    public function storedFiles(): HasMany
    {
        return $this->hasMany(StoredFile::class);
    }

    public function pedagogicalResources(): HasMany
    {
        return $this->hasMany(PedagogicalResource::class);
    }
}
