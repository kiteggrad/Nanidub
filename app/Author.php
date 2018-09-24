<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Author extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'author',
    ];

    public function animes()
    {
        return $this->hasMany('App\anime');
    }
}
