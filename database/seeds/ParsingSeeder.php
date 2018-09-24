<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Library\AnimeParser;


class ParsingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        $animes = AnimeParser::parseAllAnime();
        foreach ($animes as $anime) {
            $anime->addToDB();
        }


    }
}