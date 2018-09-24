<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Anime_category extends Model
{

    public function anime() {
        return $this->belongsTo('App\Anime');
    }
}
