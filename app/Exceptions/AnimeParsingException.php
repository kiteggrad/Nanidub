<?php

namespace App\Exceptions;

use Exception;

class AnimeParsingException extends Exception
{/** @todo Доделать эксепшен */
    public $link;

    public function __construct($link)
    {
        $this->link = $link;
    }

    public function report() {
        DB::beginTransaction();

    }
}