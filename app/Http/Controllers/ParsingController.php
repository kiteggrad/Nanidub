<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class ParsingController extends Controller
{
	function __construct()
	{
    	//ini_set ('memory_limit', '10240M');// максимальный размер памяти
		//ini_set('max_execution_time', 100500);// максимальное время выполнения скрипта
	}

	static public function show()
	{
		return view('admin/parsing');
	}

	static public function validateDate(string $_date)
	{
		if(preg_match('!сегодня!ui', $_date)) {
			$time = explode(', ', $_date)[1];

			return date('Y-m-d').' '.$time.':00';
		}
		elseif (preg_match('!вчера!ui', $_date)) {
			$time = explode(', ', $_date)[1];

			return date('Y-m-d', strtotime('yesterday')).' '.$time.':00';
		}
		else {
			$time = explode(', ', $_date)[1];
			$date = explode(', ', $_date)[0];
			$date_arr = explode('-', $date);

			if(iconv_strlen($date_arr[0])==1) {
				$day = '0'.$date_arr[0];
			}
			else $day = $date_arr[0];

			$true_date = $date_arr[2].'-'.$date_arr[1].'-'.$day;

			return $true_date.' '.$time.':00';
		}
		// 2018-06-10 12:10:00 // валидно

		// Сегодня, 12:10
		// Вчера, 17:42
		// 8-06-2018, 23:14
	}

	static public function showParseResult()
	{
		// тестовый парсинг аниме
		if(isset($_POST['testParse'])){
			$parse_errors = array();
			$parse_result = parseAnime($_POST['link'], $parse_errors);
			$data_arr = array(
				'parse_result' => $parse_result,
				'parse_errors' => $parse_errors,
			);
		}

		// парсинг всех аниме и сохранение в бд
		elseif (isset($_POST['parseAll'])) {
			ini_set ('memory_limit', '10240M');// максимальный размер памяти
	 	 	ini_set('max_execution_time', 100500);// максимальное время выполнения скрипта
			$parse_errors = array();
			parseAllAnime($parse_errors);
			$data_arr = array(
				'parse_errors' => $parse_errors,
			);
		}

		// итерация по страницам каталога аниме
		elseif (isset($_POST['iter'])) {
			ini_set ('memory_limit', '10240M');// максимальный размер памяти
	 	 	ini_set('max_execution_time', 100500);// максимальное время выполнения скрипта

	 	 	$iter_result = pagesIteration(function($page_html){

	 	 		// Тело итерации

		 	 		//return $page_html->find('span[class=navigation]', 0)->find('span[!class]', 0)->plaintext;
		 	 		$return_arr = array();

		 	 		$animes_info = $page_html->find('div.newsinfo');

		 	 		foreach ($animes_info as $key => $anime_info) {

		 	 			$title = $page_html->find('h2[class=title]', $key)->find('a', 0)->plaintext;
		 	 			$title = explode('/', $title);
						//если есть слеш в названии
							if(count($title)==3) {
								$title[0]=$title[0].' / '.$title[1];
								$title[1]=$title[2];
							}

		 	 			$anime_data['name'] = trim($title[0]);
		 	 			$anime_data['date'] = $anime_info->find('div[class=str]', 0)->plaintext;
		 	 			$return_arr[] = $anime_data;
		 	 			$query = DB::connection('mysql_parsed')->table('parsed_animes')->where('name', '=', $anime_data['name']);
		 	 			$query->update(['created_at'=>self::validateDate($anime_data['date'])]);
		 	 		}

		 	 		return $return_arr;

	 	 	});

			$data_arr = array(
				'iter_result' => $iter_result,
			);
		}
		return view('admin/parsing', $data_arr);
	}
}







	