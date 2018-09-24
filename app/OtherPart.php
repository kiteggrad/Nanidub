<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OtherPart extends Model
{
    public $timestamps = false;
    protected $table = 'otherParts';
    protected $fillable = [
        'sourceLink',
        'linkText',
        'fullText',
        'checked',
    ];
}
