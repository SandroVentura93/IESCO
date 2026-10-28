<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['title','slug'];

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }
}
