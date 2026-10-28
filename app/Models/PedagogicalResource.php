<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PedagogicalResource extends Model
{
    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/webp',
        'video/mp4',
        'audio/mpeg',
        'application/zip',
    ];

    protected $table = 'pedagogical_resources';

    protected $fillable = ['user_id', 'course_id', 'week', 'name', 'path', 'mime', 'size'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function storedFile(): HasOne
    {
        return $this->hasOne(StoredFile::class, 'path', 'path')
            ->where('section', 'Recursos_Pedagogicos');
    }
}
