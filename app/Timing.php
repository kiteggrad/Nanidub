<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Timing extends Model
{
    public $timestamps = false;
    protected $fillable = ['timing'];

    public function animes()
    {
        return $this->belongsToMany('App\anime');
    }
}
