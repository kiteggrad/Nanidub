<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Str;

class Anime extends Model
{

    public function country()
    {
        return $this->belongsTo('App\country');
    }

    public function producer()
    {
        return $this->belongsTo('App\producer');
    }

    public function author()
    {
        return $this->belongsTo('App\author');
    }

    public function studio()
    {
        return $this->belongsTo('App\studio');
    }

    public function categories()
    {
        return $this->belongsToMany('App\category');
    }

    public function years()
    {
        return $this->belongsToMany('App\year');
    }

    public function genres()
    {
        return $this->belongsToMany('App\genre');
    }

    public function dubbings()
    {
        return $this->belongsToMany('App\dubbing');
    }

    public function timings()
    {
        return $this->belongsToMany('App\timing');
    }

    public function translates()
    {
        return $this->belongsToMany('App\translate');
    }

    public function episodeLinks()
    {
        return $this->hasMany('App\EpisodeLink');
    }

    public function otherParts()
    {
        return $this->hasMany('App\OtherPart');
    }
}