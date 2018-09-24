<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Translate extends Model
{
    public $timestamps = false;
    protected $fillable = ['translate'];

    public function animes()
    {
        return $this->belongsToMany('App\anime');
    }
}
