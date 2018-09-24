<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Anime_genre extends Model
{
    public function animes()
    {
        return $this->belongsToMany('App\anime');
    }
}
