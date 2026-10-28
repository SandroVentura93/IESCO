<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['menu_id','title','url','position'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
