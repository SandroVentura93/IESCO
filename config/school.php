<?php

// ============================================================
//  config/school.php — Configuración del Sistema Web IE
// ============================================================
// Edite este archivo con los datos de su institución.
// Las claves sensibles (API Key Drive) van en .env
// ============================================================

return [

    // ─────────────────────────────────────────────
    //  DATOS DE LA INSTITUCIÓN
    // ─────────────────────────────────────────────
    'name'       => env('SCHOOL_NAME',       'Institución Educativa Pública "Salomón Cáceres Oliva" Tumbadén'),
    'short_name' => env('SCHOOL_SHORT_NAME', 'IE.: "S.C.O." Tumbadén'),
    'motto'      => env('SCHOOL_MOTTO',      'Disciplina · Esfuerzo · Superación'),
    'director'   => env('SCHOOL_DIRECTOR',   ''),
    'address'    => env('SCHOOL_ADDRESS',    'Tumbadén, San Pablo, Cajamarca — Perú'),
    'phone'      => env('SCHOOL_PHONE',      '+51 000 000 000'),
    'email'      => env('SCHOOL_EMAIL',      'iescocajamarca@gmail.com'),
    'year'       => env('SCHOOL_YEAR',       '2026'),
    'timezone'   => env('SCHOOL_TIMEZONE',  'America/Lima'),

    // ─────────────────────────────────────────────
    //  COLORES  (verde y rojo del logo institucional)
    // ─────────────────────────────────────────────
    'colors' => [
        // Color primario: verde del escudo
        'primary'   => env('SCHOOL_COLOR_PRIMARY',   '#17683e'),
        // Color secundario/acentos: dorado del borde del escudo
        'secondary' => env('SCHOOL_COLOR_SECONDARY', '#b88934'),
        // Color terciario: rojo del escudo
        'tertiary'  => env('SCHOOL_COLOR_TERTIARY',  '#c41e1e'),
    ],

    // ─────────────────────────────────────────────
    //  REDES SOCIALES  (deje '#' si no aplica)
    // ─────────────────────────────────────────────
    'social' => [
        'facebook'  => env('SCHOOL_SOCIAL_FACEBOOK',  '#'),
        'instagram' => env('SCHOOL_SOCIAL_INSTAGRAM', '#'),
        'youtube'   => env('SCHOOL_SOCIAL_YOUTUBE',   '#'),
        'twitter'   => env('SCHOOL_SOCIAL_TWITTER',   '#'),
    ],

    // ─────────────────────────────────────────────
    //  ESTADÍSTICAS
    // ─────────────────────────────────────────────
    'stats' => [
        'students' => (int) env('SCHOOL_STAT_STUDENTS', 220),
        'teachers' => (int) env('SCHOOL_STAT_TEACHERS', 18),
        'years'    => (int) env('SCHOOL_STAT_YEARS',    35),
        'projects' => (int) env('SCHOOL_STAT_PROJECTS', 4),
    ],

    // ─────────────────────────────────────────────
    //  GOOGLE DRIVE
    // ─────────────────────────────────────────────
    'drive' => [
        'api_key'                => env('GOOGLE_DRIVE_API_KEY',            ''),
        'proyecto_folder_id'     => env('GOOGLE_DRIVE_PROYECTO_FOLDER_ID', ''),
        'novedades_folder_id'    => env('GOOGLE_DRIVE_NOVEDADES_FOLDER_ID',''),
        'publicaciones_folder_id'=> env('GOOGLE_DRIVE_PUBLICACIONES_FOLDER_ID',''),
        'max_files'              => (int) env('GOOGLE_DRIVE_MAX_FILES', 30),
    ],

    // ─────────────────────────────────────────────
    //  MODO DEMO
    //  true  → muestra datos de ejemplo (sin Drive)
    //  false → usa la API real de Google Drive
    // ─────────────────────────────────────────────
    'demo_mode' => (bool) env('SCHOOL_DEMO_MODE', true),
];
