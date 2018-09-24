<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Dubbing extends Model
{
    public $timestamps = false;
    protected $fillable = ['dubbing'];

    public function animes()
    {
        return $this->belongsToMany('App\anime');
    }
}
