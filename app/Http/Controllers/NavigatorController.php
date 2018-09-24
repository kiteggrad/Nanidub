<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use App\Anime;
use App\Genre;
use App\Category;
use App\Country;
use App\Producer;
use App\Author;
use App\Studio;
use App\Year;
use App\Dubbing;
use App\Timing;
use App\Translate;

class NavigatorController extends Controller
{
    public function show()
    {
    	$data_arr = [
    		'genres' => Genre::all(),
    		'categories' => Category::all(),
    		'countries' => Country::all(),
    		'producers' => Producer::all(),
    		'authors' => Author::all(),
    		'studios' => Studio::all(),
    		'years' => Year::all(),
    		'dubbings' => Dubbing::all(),
    		'timings' => Timing::all(),
    		'translates' => Translate::all(),
    	];
    	return view('navigation', $data_arr);
    }
    public function shows()
    {
    	dump($_POST);

    	DB::enableQueryLog();

    	$categoriesIds = $_POST['categories']??false;
    	$genresIds = $_POST['genres']??false;
    	$countriesIds = $_POST['countries']??false;
    	$producersIds = $_POST['producers']??false;
    	$studiosIds = $_POST['studios']??false;
    	$dubbingsIds = $_POST['dubbings']??false;
    	$authorsIds = $_POST['authors']??false;
    	$yearsIds = $_POST['years']??false;
    	$timingsIds = $_POST['timings']??false;
    	$translatesIds = $_POST['translates']??false;

    	$orderBy = $_POST['orderBy'];

    	$search_text = $_POST['search_text'];

    	$animes = new Anime;
    	$start = microtime(true);
    	
		if($categoriesIds) $animes = $animes->whereHas('categories', function($query) use ($categoriesIds) {
			$query->whereIn('id', $categoriesIds);
		});
		if($genresIds) $animes = $animes->whereHas('genres', function($query) use ($genresIds) {
			$query->whereIn('id', $genresIds);
		});
		if($countriesIds) $animes = $animes->whereHas('country', function($query) use ($countriesIds) {
			$query->whereIn('id', $countriesIds);
		});
		if($producersIds) $animes = $animes->whereHas('producer', function($query) use ($producersIds) {
			$query->whereIn('id', $producersIds);
		});
		if($studiosIds) $animes = $animes->whereHas('studio', function($query) use ($studiosIds) {
			$query->whereIn('id', $studiosIds);
		});
		if($dubbingsIds) $animes = $animes->whereHas('dubbings', function($query) use ($dubbingsIds) {
			$query->whereIn('id', $dubbingsIds);
		});
		if($authorsIds) $animes = $animes->whereHas('author', function($query) use ($authorsIds) {
			$query->whereIn('id', $authorsIds);
		});
		if($yearsIds) $animes = $animes->whereHas('years', function($query) use ($yearsIds) {
			$query->whereIn('id', $yearsIds);
		});
		if($timingsIds) $animes = $animes->whereHas('timings', function($query) use ($timingsIds) {
			$query->whereIn('id', $timingsIds);
		});
		if($translatesIds) $animes = $animes->whereHas('translates', function($query) use ($translatesIds) {
			$query->whereIn('id', $translatesIds);
		});

		if($orderBy == 'По дате добавления') $animes->orderBy('created_at', 'desc');
		else if($orderBy == 'По рейтингу') $animes->orderBy('rating', 'desc');

    	$search_like_text = str_replace(" ", "%", $search_text);
		$animes = $animes->where('name', 'like', "%$search_like_text%");

		$animes = $animes->get();
		$time = microtime(true) - $start;



    	dump($animes);
    	dump(DB::getQueryLog());
    	dump($time);
    }
}
/*
categories
genres
countries
producers
studios
dubbings
authors
years
timings
translates
*/
