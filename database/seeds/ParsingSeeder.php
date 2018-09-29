<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Library\AnimeParser;
use Library\AnimeParser\AnidubSource;
use Library\AnimeParser\AnidubParser;
use App\Anime;


class ParsingSeeder extends Seeder
{

    public function run()
    {
        $animes = AnidubParser::parseAll();

        foreach ($animes as $anime) {
            Anime::saveParsed($anime);
        }
    }
}