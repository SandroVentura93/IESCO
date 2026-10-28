<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key','value'];

    public static function get($key, $default = null)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) return $default;
        $s = static::where('key', $key)->first();
        return $s ? $s->value : $default;
    }

    public static function set($key, $value)
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) return null;
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
