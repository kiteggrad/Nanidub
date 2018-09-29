<?php

use App\User;
use Illuminate\Support\Facades\DB;
use Library\AnimeParser;
//use Library\Requester\Requester;

Route::get('/', function () {
    //return view('welcome');
    \VideoGrabber\VideoGrabber::check();
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
        'https://online.anidub.com/anime/10193-duh-v-stalnoy-ploti-u-istokov-alternativnaya-arhitektura-koukaku-kidoutai-arise-alternative-architecture-04-iz-10.html',
    ];
    foreach ($links as $link) {
        //dump(new AnimeParser($link, $link));
        //dump(getClient($link)->get($link));
        //dump(get_headers($link));
    }

    foreach (\App\EpisodeLink::all() as $episode_link) {
        $testLinks[] = $episode_link->episodeLink;
    }

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
