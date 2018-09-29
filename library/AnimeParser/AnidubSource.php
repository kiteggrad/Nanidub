<?php

namespace Library\AnimeParser;

use DiDom\Document;
use Library\Requester\Requester;

class AnidubSource extends AnimeSource
{
    public static function getLoginData(): array
    {
        $login_data = array(
            'form_params' => [
                'login_name' => env('ANIDUB_LOGIN'),
                'login_password' => env('ANIDUB_PASSWORD'),
                'login' => 'submit',
            ],
        );
        return $login_data;
    }
}