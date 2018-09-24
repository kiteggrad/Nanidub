<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ParsedAnimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parsed_animes', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->increments('id');
            $table->timestamps();

            $table->text(   'categories')           ->nullable();
            $table->string( 'name', 255)            ->nullable();
            $table->string( 'original_name', 255)   ->nullable();
            $table->string( 'series_released', 255) ->nullable();
            $table->string( 'years', 255)           ->nullable();
            $table->string( 'genres', 255)          ->nullable();
            $table->string( 'countries', 255)       ->nullable();
            $table->string( 'series_count', 255)    ->nullable();
            $table->string( 'release_date', 255)    ->nullable();
            $table->string( 'producers', 255)       ->nullable();
            $table->string( 'authors', 255)         ->nullable();
            $table->string( 'dubbings', 255)        ->nullable();
            $table->string( 'timings', 255)         ->nullable();
            $table->string( 'translates', 255)      ->nullable();
            $table->string( 'studios', 255)         ->nullable();
            $table->string( 'poster', 255)          ->nullable();
            $table->float(  'rating')               ->nullable();
            $table->text(   'description')          ->nullable();
            $table->text(   'episodes')             ->nullable();
            $table->text(   'series_links')         ->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parsed_animes');
    }
}
/*

*/