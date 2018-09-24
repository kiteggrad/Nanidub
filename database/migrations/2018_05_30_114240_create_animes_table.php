<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @todo добавить парные первичные ключи */

        Schema::create('countries', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->tinyIncrements('id');
            $table->string('country', 15)->unique();
        });

        Schema::create('producers', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('producer', 50)->unique();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('author', 50)->unique();
        });

        Schema::create('studios', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('studio', 50)->unique();
            $table->text('poster');
        });

        //DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::create('animes', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->timestamps();

            $table->string('name', 255)->nullable();
            $table->string('originalName', 255)->nullable();
            $table->string('episodesReleased', 255)->nullable();
            $table->unsignedTinyInteger( 'country_id')->nullable();
            $table->string('episodesCount', 5)->nullable();
            $table->date('releaseDateFrom')->nullable();
            $table->date('releaseDateTo')->nullable();
            $table->unsignedSmallInteger('producer_id')->nullable();
            $table->unsignedSmallInteger('author_id')->nullable();
            $table->unsignedSmallInteger('studio_id')->nullable();
            $table->string('poster', 255)->nullable();
            $table->float('rating')->nullable();
            $table->text('description')->nullable();
            $table->text('episodesList')->nullable();
            $table->string('sourceLink', 255)->unique();
            $table->string('slug', 255)->unique();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('producer_id')->references('id')->on('producers')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->foreign('studio_id')->references('id')->on('studios')->onDelete('cascade');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('category', 50)->unique();
        });

        Schema::create('anime_category', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');
            $table->unsignedSmallInteger('category_id');

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
        });

        Schema::create('years', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->unsignedSmallInteger('year')->unique();

        });

        Schema::create('anime_year', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');
            $table->unsignedSmallInteger('year_id');

            $table->foreign('year_id')->references('id')->on('years')->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
        });

        Schema::create('genres', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('genre', 50)->unique();
        });

        Schema::create('anime_genre', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');
            $table->unsignedSmallInteger('genre_id');

            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
        });

        Schema::create('dubbings', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('dubbing', 50)->unique();
        });

        Schema::create('anime_dubbing', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');
            $table->unsignedSmallInteger('dubbing_id');

            $table->foreign('dubbing_id')->references('id')->on('dubbings')->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
        });

        Schema::create('timings', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('timing', 50)->unique();
        });

        Schema::create('anime_timing', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');
            $table->unsignedSmallInteger('timing_id');

            $table->foreign('timing_id')->references('id')->on('timings')->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
        });

        Schema::create('translates', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->smallIncrements('id');
            $table->string('translate', 50)->unique();
        });

        Schema::create('anime_translate', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');
            $table->unsignedSmallInteger('translate_id');

            $table->foreign('translate_id')->references('id')->on('translates')->onDelete('cascade');
            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
        });

        Schema::create('players', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->tinyIncrements('id');
            $table->string('player', 50)->unique();
        });

        Schema::create('episodeLinks', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');
            $table->unsignedTinyInteger('player_id');

            $table->string('episodeLink', 255)->unique();
            $table->unsignedSmallInteger('number')->default(1);
            $table->string('episodeText', 255)->nullable();

            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
        });

        Schema::create('otherParts', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->unsignedSmallInteger('anime_id');

            $table->string('sourceLink', 255)->nullable();
            $table->string('linkText', 255)->nullable(); // checked будет иметь slug
            $table->string('fullText', 255);
            $table->boolean('checked')->default(false);

            $table->foreign('anime_id')->references('id')->on('animes')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otherParts');
        Schema::dropIfExists('anime_category');
        Schema::dropIfExists('anime_year');
        Schema::dropIfExists('anime_genre');
        Schema::dropIfExists('anime_dubbing');
        Schema::dropIfExists('anime_timing');
        Schema::dropIfExists('anime_translate');
        Schema::dropIfExists('episodeLinks');
        Schema::dropIfExists('players');
        Schema::dropIfExists('animes');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('producers');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('studios');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('years');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('dubbings');
        Schema::dropIfExists('timings');
        Schema::dropIfExists('translates');
    }
}

// название параметра              // название столбца         // название таблицы     // таблица many to many
// 
// 'Категория',                    //                          // categories           // animes_categories
// 'Название',                     // name                     //                      // 
// 'Оригинальное название',        // original_name            //                      // 
// 'Выпущено серий',               // series_released          //                      // 
// 'Год',                          //                          // years                // animes_year
// 'Жанр',                         //                          // genres               // animes_genre
// 'Страна',                       // countrys_id               // countries            // 
// 'Количество серий',             // series_count             //                      // 
// 'Дата выпуска',                 // release_date             //                      // 
// 'Режиссер',                     // producers_id              // producers            // 
// 'Автор оригинала / Сценарист',  // authors_id                // authors              // 
// 'Озвучивание',                  //                          // dubbings             // animes_dubbing
// 'Тайминг и работа со звуком',   //                          // timings              // animes_timing
// 'Перевод',                      //                          // translates           // animes_translate
// 'Студия',                       // studios_id                // studios              // 
// 'Постер',                       // poster                   //                      // 
// 'Рейтинг',                      // rating                   //                      // 
// 'Описание',                     // description              //                      // 
// 'Эпизоды',                      // episodes                 //                      // 
// 'series_links',                 // series_links             //                      // 