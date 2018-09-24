<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Producer extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'producer',
    ];

	public function animes()
    {
        return $this->hasMany('App\anime');
    }
}
