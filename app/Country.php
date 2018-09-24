<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Country extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'country',
    ];

	public function animes()
    {
        return $this->hasMany('App\anime');
    }
}
