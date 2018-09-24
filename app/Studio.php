<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Studio extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'studio',
    ];

    public function animes()
    {
        return $this->hasMany('App\anime');
    }
}
