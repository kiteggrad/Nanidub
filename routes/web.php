<?php

use App\User;
use Illuminate\Support\Facades\DB;
use Library\AnimeParser;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {

    Route::get('/', ['uses' => 'AdminController@show']);
    Route::get('parsing', ['uses' => 'ParsingController@show']);
    Route::post('parsing', ['uses' => 'ParsingController@showParseResult']);

});

Route::get('/test', function() {
    ini_set ('memory_limit', '10240M');//
    ini_set('max_execution_time', 100500);//
    $links = [
        'https://online.anidub.com/anime/10517-figurnyy-kaleydoskop-ginban-kaleidoscope-01-iz-12.html'
        //'https://online.anidub.com/anime_ova/anime_ona/10411-lazurnyy-strelok-ganvolt-armed-blue-gunvolt.html'
        //'https://online.anidub.com/anime/unclosed/9071-inuyasha-inuyasha-10-iz-167.html'
        //'https://online.anidub.com/anime_movie/10598-naruto-posledniy-film.html',
        //'https://online.anidub.com/anime_movie/9516-mobilnyy-voin-zeta-gandam-novyy-perevod-film-pervyy-nasledniki-zvezd-mobile-suit-zeta-gundam-a-new-translation-heirs-to-the-stars-.html',
    ];
    foreach ($links as $link) {
        dump(new AnimeParser($link, $link));
    }

//    $animes = AnimeParser::parseAllAnime();
//    foreach ($animes as $anime) {
//        $anime->addToDB();
//    }




});

Route::group(['prefix' => 'navigation', 'middleware' => 'auth'], function () {

	Route::get('/', ['uses' => 'NavigatorController@show']);
	Route::post('/', ['uses' => 'NavigatorController@shows']);

});

// Authentication Routes...
Route::get('login', 'Auth\MyLoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\MyLoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\MyRegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\MyRegisterController@register');
Route::get('register/{confirm_token}', 'Auth\MyRegisterController@confirm');


// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('/noAccess', function() {
	return view('auth.noAccess');
});

/*

UPDATE `nanidub`.parsed_animes, `parsed_nanidub`.`parsed_animes`
SET `nanidub`.parsed_animes.created_at = `parsed_nanidub`.`parsed_animes`.created_at
WHERE `nanidub`.`parsed_animes`.`name` = `parsed_nanidub`.`parsed_animes`.`name`
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');*/
