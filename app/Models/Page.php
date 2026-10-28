<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title','slug','section','content','published','position','featured_image','featured_thumbnail'];

    protected $casts = [
        'published' => 'boolean',
        'position' => 'integer',
    ];

    public static function sections(): array
    {
        return [
            'inicio' => 'Inicio',
            'quienes-somos' => 'Quiénes somos',
            'proyecto' => 'Proyecto IJENT',
            'novedades' => 'Novedades',
            'publicaciones' => 'Publicaciones',
            'institucional' => 'Páginas institucionales',
        ];
    }

    public function getSectionLabelAttribute(): string
    {
        return self::sections()[$this->section] ?? 'Sin sección';
    }
}
