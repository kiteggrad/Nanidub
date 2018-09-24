<?php

namespace Library;

use App\EpisodeLink;
use App\OtherPart;
use App\Player;
use DiDom\Element;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Promise;
use DiDom\Document;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Constraint\Count;
use Psr\Http\Message\ResponseInterface;
use App\Anime,
    App\Anime_category,
    App\Anime_dubbing,
    App\Anime_genre,
    App\Anime_timing,
    App\Anime_translate,
    App\Year_anime,
    App\Author,
    App\Category,
    App\Country,
    App\Dubbing,
    App\Genre,
    App\Parsed_animes,
    App\Producer,
    App\Role,
    App\Studio,
    App\Timing,
    App\Translate,
    App\Year;
use Illuminate\Support\Facades\DB;

function getClient($base_uri)
{
    $user_agent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36';
    // $context  = stream_context_create(array('http' => array('user_agent' => $user_agent)));

    $client = new Client([
        // Базовый URI используется с относительными запросами
        'base_uri' => $base_uri,
        'User_Agent' => $user_agent, // чёт походу не работает
        //'verify' => false,
        // Вы можете установить любое количество параметров запроса по умолчанию.
        //' timeout '   =>  2.0 ,
    ]);
    return $client;
}

/**
 * Данные для входа в аккаунт
 */
function getAnidubLogin_data()
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

function getPostPromises(array $links, array $post_data)
{

    $promises = array();
    $client = getClient($links[0]);
    foreach ($links as $link) {
        $promise = $client->postAsync($link, $post_data);
        $promises[$link] = $promise;
    }

    return $promises;
}

function getCategories(Element $doc, string $sourceLink) {

    try {
        $categories = $doc->first('div.newsinfo')->find('a');
        array_shift($categories); // убирает "Автор: kOsjaK"
        foreach ($categories as $key => &$category) {
            $category = $category->text();
        }
        return $categories;

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить категории аниме: ' . $sourceLink);
        return null;
    }
}

function getName_EpisodesReleased(Element $doc, string $sourceLink) {

    $returnArray['Название'] = null;
    $returnArray['Оригинальное название'] = null;
    $returnArray['Выпущено серий'] = null;

    try {
        $title = $doc->first('div.title')->first('h1.titlfull')->text();
        $title = explode('/', $title);

        //если есть слеш в названии
        if (count($title) == 3) {
            $title[0] = $title[0] . ' / ' . $title[1];
            $title[1] = $title[2];
        }

        $returnArray['Название'] = trim($title[0]);
        if (isset($title[1])) {
            preg_match('![a-z0-9]+[^\\[]+!ui', $title[1], $match);
            $returnArray['Оригинальное название'] = trim($match[0]);
            preg_match('![0-9]+ из [0-9a-zа-я]+!ui', $title[1], $match);
            if (isset($match[0])) $returnArray['Выпущено серий'] = $match[0];
        }

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить имя аниме: ' . $sourceLink);
    }

    return $returnArray;

}

function getKeysInfo(Element $doc, string $sourceLink) {

    $returnArray = [];
    $infoKeys = [
        //'Альтернативные названия' => 'multiple',
        'Год' => 'multiple',
        'Жанр' => 'multiple',
        'Страна' => 'single',
        'Количество серий' => 'single',
        'Дата выпуска' => 'single',
        'Режиссер' => 'single',
        'Автор оригинала / Сценарист' => 'single',
        'Озвучивание' => 'multiple',
        'Тайминг и работа со звуком' => 'multiple',
        'Перевод' => 'multiple',
    ];

    try {
        $info = $doc->first('ul[class=reset]');

        foreach ($info->find('li') as $value) {

            $infoString = $value->text();

            foreach ($infoKeys as $key => $count) {

                if (!isset($returnArray[$key])) {
                    if (preg_match("!$key:!ui", $infoString)) {

                        $infoString = strip_tags($infoString);
                        $infoString = trim($infoString);

                        $result = explode(': ', $infoString);
                        array_shift($result);
                        $result = explode(', ', $result[0]);
                        //$result[0] = trim($result[0]);
                        $returnArray[$key] = $count === 'multiple' ? $result : $result[0];
                    }
                }
            }
        }
    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить информацию по ключам: ' . $sourceLink);
    }

    return $returnArray;
}

function getStudio(Element $doc, string $sourceLink) {

    $studio_name = null;
    $studio_poster = null;

    try {
        $info = $doc->first('ul[class=reset]');
        $video_info = $info->first('li.video_info');

        if($video_info) {
            $el_studio = $video_info->first('img');
            $studio_name = $el_studio->alt;
            $studio_poster = $el_studio->src;
        }
        else return null;

        return [
            'name' => $studio_name,
            'poster' => $studio_poster,
        ];

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить студию: ' . $sourceLink);
        return null;
    }
}

function getPoster(Element $doc, string $sourceLink) {

    try {
        $poster = $doc->first('div.poster_img img')->src;
        return $poster;

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить постер: ' . $sourceLink);
        return null;
    }
}

function getRating(Element $doc, string $sourceLink) {

    try {
        $rating = $doc->first('div.rate_view');

        if ($rating == null) return null;
        else {
            $rating = $rating->first('b')->text();
            if (is_numeric($rating)) $rating = $rating * 2;
            else return null;
        }

        return $rating;

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить рейтинг: ' . $sourceLink);
        return null;
    }
}

function getDescription(Element $doc, string $sourceLink) {

    try {
        $description = explode('Описание: ', $doc->text());
        array_shift($description);

        if (isset($description[0])) {
            $description = $description[0];
            $index = strpos($description, 'Эпизоды');
            if ($index === false) $index = strpos($description, 'Подпишись на');
            $description = substr($description, 0, $index);

            $description = trim($description);
        }

        return $description;

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить описание: ' . $sourceLink);
        return null;
    }
}

function getEpisodesLinks(Element $doc, string $sourceLink) {

    try {
        $returnArray = [];

        $playerSelectors = [
            'sel',
            'sel2',
            'sel3',
        ];

        foreach ($playerSelectors as $playerSelector) {
            $el_selector = $doc->first("#$playerSelector");
            if ($el_selector == null) continue;

            $el_videos = $el_selector->find('option');

            foreach ($el_videos as $key => $el_video) {
                $video_string = $el_video->value;
                $index = strpos($video_string, '|');
                $video_id = substr($video_string, $index + 1);
                $video_link = substr($video_string, 0, $index);
                $player = null;
                preg_match('@(https{0,1}://|//|/)(.*?\.)?(.+?)(\..*)(/)@ui' , $video_link,$player);

                if($player[3] ?? false) {
                    $player = $player[3];

                    // разные серии имеют одну ссылку
                    if(isset($returnArray[$player]) && in_array($video_link, array_column($returnArray[$player], 'link'))) {
                        Log::channel('parsing')->info("Разные серии имеют одну ссылку. Source: $sourceLink Плеер: $player Ссылка на видео: $video_link");
                        continue;
                    }
                    else {
                        $returnArray[$player][$video_id]['link'] = $video_link;
                        $returnArray[$player][$video_id]['text'] = $el_video->text();
                    }
                }
                else throw new \Exception("не удалось вытянуть название плеера из ссылки: $video_link");
            }
        }

        if ($returnArray == []) { // нет селекторов - фильм
            $src = $doc->first('#vk1')->first('iframe')->src;
            if($src != '') {
                $player = null;
                preg_match('@(https{0,1}://|//|/)(.*?\.)?(.+?)(\..*)(/)@ui' , $src,$player);

                if($player[3] ?? false) {
                    $player = $player[3];

                    return [
                        $player => [ 1 => ['link' => $src, 'text' => null]]
                    ];
                }
                else throw new \Exception("не удалось вытянуть название плеера из ссылки: $src");
            }
            else throw new \Exception('нет ссылки на серию в iframe');
        }

        return $returnArray;

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info("Не удалось получить ссылки на серии аниме: $sourceLink message: " . $exception->getMessage());
        return [];
    }
}

function getEpisodesList(Element $doc, string $sourceLink) {
    /** @todo Получение списка эпизодов */
    try {
    /*$episodes = $doc->first('div[style=display:inline;]')->find('span');
    if ($episodes) {
        $episodes = $episodes->text();
        $episodes = explode("\r\n", $episodes);
        $returnArray['Эпизоды'] = $episodes;
    }*/

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить список эпизодов: ' . $sourceLink);
        return null;
    }

    return null;
}

function getOtherParts(Element $doc, string $sourceLink) {

    try {
        $returnArray = [];

        foreach ($doc->first('div.tags')->find('span') as $span) {
            $_arr = [];
            $_arr['fullText'] = null;
            $_arr['part_link'] = null;
            $_arr['part_link_text'] = null;

            $_arr['fullText'] = $span->text();
            if ($_arr['fullText'] == null) continue;
            if ($_arr['fullText'] == '(Лицензия)') continue;

            $part_link_el = $span->first('a');

            // если есть ссылка на другую часть
            if ($part_link_el) {
                if($part_link_el->text() == '+1') {
                    $_arr['fullText'] = preg_replace('!\s\+1$!ui', '', $_arr['fullText']);
                }
                else {
                    $_arr['part_link'] = $part_link_el->href;
                    $_arr['part_link_text'] = $part_link_el->text();
                }
            }

            $returnArray[] = $_arr;
        }

        return $returnArray;

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить другие части аниме: ' . $sourceLink);
        return [];
    }
}

function generateSlug(array $elements) {

    $slug = '';
    $string ='';
    foreach ($elements as $element) {
       $string .= ' ' . $element;
    }
    $slug = str_slug($string);

    return $slug;
}

function getNormalDate(string $date) {

    $dd = null;
    $mm = null;
    $yy = null;

    preg_match('@^[0-9]{2}@ui', $date, $dd);
    preg_match('@(?:\.)([0-9]{2})(?:\.)@ui', $date, $mm);
    preg_match('@[0-9]{4}$@ui', $date, $yy);

    return $yy[0].$mm[1].$dd[0];
}

function getReleaseDates(string $releaseDate, $sourceLink) {

    try {
        $releaseDate = preg_replace('!c!ui', 'с', $releaseDate);
        $dates['from'] = null;
        $dates['to'] = null;

        $match = [];
        if (preg_match('/(?:с\s)([0-9]{2}\.[0-9]{2}\.[0-9]{4})/ui', $releaseDate,$match)) {
            $dates['from'] = getNormalDate($match[1]);
            if (preg_match('/(?:по\s)([0-9]{2}\.[0-9]{2}\.[0-9]{4})/ui', $releaseDate,$match)) {
                $dates['to'] = getNormalDate($match[1]);
            }
        }
        else if (preg_match('![0-9]{2}\.[0-9]{2}\.[0-9]{4}!ui', $releaseDate,$match)) {
            $dates['from'] = getNormalDate($match[0]);
            $dates['to'] = getNormalDate($match[0]);
        }

    } catch (\Throwable $exception) {
        Log::channel('parsing')->info('Не удалось получить даты релиза: ' . $sourceLink);
        $dates['from'] = null;
        $dates['to'] = null;
        return $dates;
    }

    return $dates;
}

function getAnidubHtml($link) {

    $client = getClient($link);
    $request = $client->request('POST', $link, getAnidubLogin_data());
    $html = $request->getBody()->getContents();

    return $html;
}

class AnimeParser
{
    public $categories = [];
    public $name = null;
    public $originalName = null;
    public $episodesReleased = null;
    public $alternativeNames = [];
    public $years = null;
    public $genres = [];
    public $country = null;
    public $episodesCount = null;
    public $releaseDateFrom = null;
    public $releaseDateTo = null;
    public $producer = null;
    public $author = null;
    public $dubbings = [];
    public $timings = [];
    public $translates = [];
    public $studio = null;
    public $poster = null;
    public $rating = null;
    public $description = null;
    public $episodesList = null;
    public $episodeLinks = [];
    public $otherParts = [];
    public $sourceLink = null;

    public function __construct($link_or_html, string $sourceLink)
    {
        $this->parseAnime($link_or_html, $sourceLink);
    }

    public function parseAnime($link_or_html, string $sourceLink)
    {
        $doc = null;

        // проверка ссылка / html
        $isUrl = filter_var($link_or_html, FILTER_VALIDATE_URL) !== false;
        if ($isUrl) {
            $link = $link_or_html;
            $sourceLink = $link_or_html;
            $html = getAnidubHtml($link);
            $doc = new Document($html);
        } else $doc = $link_or_html;

        // Обрезал лишнее, только контент!
        $doc = $doc->first('#dle-content');

        // Получение категории
        $this->categories = getCategories($doc, $sourceLink);

        // Получение названий аниме и кол-ва выпущеных серий
        $name_EpisodesReleased = getName_EpisodesReleased($doc, $sourceLink);
        $this->name = $name_EpisodesReleased['Название'];
        $this->originalName = $name_EpisodesReleased['Оригинальное название'];
        $this->episodesReleased = $name_EpisodesReleased['Выпущено серий'];

        // Получение информации по ключам
        $keysInfo = getKeysInfo($doc, $sourceLink);
        $this->years = $keysInfo['Год'] ?? [];
        $this->genres = $keysInfo['Жанр'] ?? [];
        $this->country = $keysInfo['Страна'] ?? null;
        $this->episodesCount = $keysInfo['Количество серий'] ?? null;
        $this->producer = $keysInfo['Режиссер'] ?? null;
        $this->author = $keysInfo['Автор оригинала / Сценарист'] ?? null;
        $this->dubbings = $keysInfo['Озвучивание'] ?? [];
        $this->timings = $keysInfo['Тайминг и работа со звуком'] ?? [];
        $this->translates = $keysInfo['Перевод'] ?? [];
        //$this->alternativeNames = $keysInfo['Альтернативные названия'] ?? [];

        // Получение дат выхода
        $dates = getReleaseDates($keysInfo['Дата выпуска'] ?? '', $sourceLink);
        $this->releaseDateFrom = $dates['from'];
        $this->releaseDateTo = $dates['to'];

        // Получение студии
        $this->studio = getStudio($doc, $sourceLink);

        // Получение постера
        $this->poster = getPoster($doc, $sourceLink);

        // Получение рейтинга
        $this->rating = getRating($doc, $sourceLink);

        // Получение описания
        $this->description = getDescription($doc, $sourceLink);

        // Получение списка эпизодов
        $this->episodesList = getEpisodesList($doc, $sourceLink);

        // Получение ссылок на серии
        $this->episodeLinks = getEpisodesLinks($doc, $sourceLink);

        // Ссылка - источник
        $this->sourceLink = $sourceLink;

        // Получение отсылок к другим частям
        $this->otherParts = getOtherParts($doc, $sourceLink);

        unset($doc);
    }

    static function getAnimeLinksFromPages(int $from = 1, int $to = 9999)
    {
        $ref = 'https://online.anidub.com/sf/page:1';
        $animes_refs = array();

        // получение клиента и данных для входа
        $client = getClient($ref);
        $login_data = getAnidubLogin_data();

        // получение номера последней страницы

        $response = getAnidubHtml($ref);
        $html = new Document($response);

        $lastPage = $html->first('div.navi')->first('span.navigation')->lastChild('a')->text();

        // защита от перескока по кол-ву страниц
        if($from < 1) $from = 1;
        if ($to == null || $to > $lastPage) $to = $lastPage;

        // если все страницы распарсены
        if ($from > $lastPage) return true;

        // получение ссылок на страницы
        $pages = array();
        for ($page = $from; $page <= $to; $page++) {
            $pages[] = 'https://online.anidub.com/sf/page:' . $page;
        }

        // паралельная отправка запросов ко всем страницам
        $promises = getPostPromises($pages, $login_data);
        $results = Promise\settle($promises)->wait();

        // перебор всех ответов и получение ссылок на все аниме c этих страниц
        foreach ($results as $link => $result) {

            // неудачный запрос
            if($result['state'] != 'fulfilled') {
                Log::channel('parsing')->error('Неудачный запрос: ' . $link . ' || message: ' . $result['reason']->getMessage());
                continue;
            }

            $animeHtml = new Document($result['value']->getBody()->getContents());
            foreach ($animeHtml->find('div.newstitle') as $newstitle) {
                $ref = $newstitle->first('a')->href;
                //фильтр на пустые ссылки, блоги, новости
                $filter = $ref !== false &&
                    !preg_match('!com/videoblog/!', $ref) &&
                    !preg_match('!com/anidub_news/!', $ref);
                if ($filter) $animes_refs[] = $ref;
            }
        }

        unset($html);
        unset($animeHtml);
        return $animes_refs;
    }

    /** @return AnimeParser[] */
    static function parseAllAnime()
    {
        $animes = [];

        while(true) {
            static $startPage = 0; // def 0
            $step = 10;

            $animeLinks = self::getAnimeLinksFromPages($startPage + 1, $startPage += $step);
            if($animeLinks === true) break;
            dump($startPage);

            $promises = getPostPromises($animeLinks, getAnidubLogin_data());
            $results = Promise\settle($promises)->wait();


            foreach ($results as $link => $result) {
                // неудачный запрос
                if ($result['state'] != 'fulfilled') {
                    Log::channel('parsing')->error('Неудачный запрос: ' . $link . ' || message: ' . $result['reason']->getMessage());
                    continue;
                }

                $animeHtml = new Document($result['value']->getBody()->getContents());
                $anime = new AnimeParser($animeHtml, $link);
                if ($anime !== null) $animes[] = $anime;
            }
        }

        return $animes;
    }

    /** @todo Объединение запросов */
    function addToDB() {
        DB::beginTransaction();
        try {

            // аниме
            $m_anime = new Anime();
            $m_anime->name = $this->name;
            $m_anime->originalName = $this->originalName;
            $m_anime->episodesReleased = $this->episodesReleased;
            $m_anime->episodesCount = $this->episodesCount;
            $m_anime->episodesList = $this->episodesList;
            $m_anime->releaseDateFrom = $this->releaseDateFrom;
            $m_anime->releaseDateTo = $this->releaseDateTo;
            $m_anime->poster = $this->poster;
            $m_anime->rating = $this->rating;
            $m_anime->description = $this->description;
            if ($this->country)
                $m_anime->country_id = Country::firstOrCreate(['country' => $this->country])->id;
            else
                $m_anime->country_id = null;
            if ($this->producer)
                $m_anime->producer_id = Producer::firstOrCreate(['producer' => $this->producer])->id;
            else
                $m_anime->producer_id = null;
            if ($this->author)
                $m_anime->author_id = Author::firstOrCreate(['author' => $this->author])->id;
            else
                $m_anime->author_id = null;
            if ($this->studio)
                $m_anime->studio_id = Studio::firstOrCreate(['studio' => $this->studio['name']], ['poster' => $this->studio['poster']])->id;
            else
                $m_anime->studio_id = null;
            $m_anime->sourceLink = $this->sourceLink;
            $m_anime->slug = str_random();
            $m_anime->save();
            $m_anime->slug = generateSlug([$m_anime->id, $this->name, $this->originalName, $this->years[0] ?? '']);
            $m_anime->save();

            $anime_id = $m_anime->id;

            // годы
            $years = [];
            foreach ($this->years as $year) {
                $years[] = Year::firstOrCreate(['year' => $year]);
            }
            $m_anime->years()->saveMany($years);

            // категории
            $categories = [];
            foreach ($this->categories as $category) {
                $categories[] = Category::firstOrCreate(['category' => $category]);
            }
            $m_anime->categories()->saveMany($categories);

            // озвучивание
            $dubbings = [];
            foreach ($this->dubbings as $dubbing) {
                $dubbings[] = Dubbing::firstOrCreate(['dubbing' => $dubbing]);
            }
            $m_anime->dubbings()->saveMany($dubbings);

            // тайминг и работа со звуком
            $timings = [];
            foreach ($this->timings as $timing) {
                $timings[] = Timing::firstOrCreate(['timing' => $timing]);
            }
            $m_anime->timings()->saveMany($timings);

            // жанры
            $genres = [];
            foreach ($this->genres as $genre) {
                $genres[] = Genre::firstOrCreate(['genre' => $genre]);
            }
            $m_anime->genres()->saveMany($genres);

            // перевод
            $translates = [];
            foreach ($this->translates as $translate) {
                $translates[] = Translate::firstOrCreate(['translate' => $translate]);
            }
            $m_anime->translates()->saveMany($translates);

            // ссылки на серии
            $episodeLinks = [];
            foreach ($this->episodeLinks as $player => $episodes) {
                foreach ($episodes as $number => $episode) {

                    $episodeLinks[] = new EpisodeLink([
                        'anime_id' => $anime_id,
                        'player_id' => Player::firstOrCreate(['player' => $player])->id,
                        'episodeLink' => $episode['link'],
                        'episodeText' => $episode['text'],
                        'number' => $number
                    ]);
                }
            }
            $m_anime->episodeLinks()->saveMany($episodeLinks);

            // другие части аниме
            /** @todo Доделать */
            $otherParts = [];
            foreach ($this->otherParts as $number => $otherPart) {

                // есть ссылка на другую часть
                if ($otherPart['part_link']) {
                    $slug = Anime::where('sourceLink', $otherPart['part_link'])->first();

                    // в бд уже добавлена другая чсть
                    if ($slug) {
                        $slug = $slug->slug;
                        $otherParts[] = new OtherPart([
                            'anime_id' => $anime_id,
                            'part_sourceLink' => $otherPart['part_link'],
                            'part_linkText' => $slug,
                            'part_fullText' => $otherPart['fullText'],
                            'checked' => true,
                        ]);
                    } else {
                        $otherParts[] = new OtherPart([
                            'anime_id' => $anime_id,
                            'sourceLink' => $otherPart['part_link'],
                            'linkText' => $otherPart['part_link_text'],
                            'fullText' => $otherPart['fullText'],
                            'checked' => false,
                        ]);
                    }

                } else {
                    $otherParts[] = new OtherPart([
                        'anime_id' => $anime_id,
                        'sourceLink' => $otherPart['part_link'],
                        'linkText' => $otherPart['part_link_text'],
                        'fullText' => $otherPart['fullText'],
                        'checked' => false,
                    ]);
                }
            }
            $m_anime->otherParts()->saveMany($otherParts);

            DB::commit();

        } catch (\Throwable $exception) {
            Log::channel('parsing')->error('Не удалось добавить аниме в бд: ' . $this->sourceLink . ' ' .
                'message: ' . $exception->getMessage() . ' ' .
                'line: ' . $exception->getLine()
            );
            DB::rollBack();
        }
    }
}