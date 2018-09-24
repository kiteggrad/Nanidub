<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    public $timestamps = false;
    protected $fillable = ['category'];

    public function animes()
    {
        return $this->belongsToMany('App\anime');
    }
}
