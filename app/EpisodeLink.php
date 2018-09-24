<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EpisodeLink extends Model
{
    public $timestamps = false;
    protected $table = 'episodeLinks';
    protected $fillable = ['episodeLink', 'number' , 'player_id'];

    //
}
