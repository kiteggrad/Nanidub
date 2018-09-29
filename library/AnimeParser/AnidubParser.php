<?php

namespace Library\AnimeParser;

use DiDom\Document;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Library\Requester\Requester;

class AnidubParser extends AnimeParser
{
    protected $sourceClassName = AnidubSource::class;

    public static function getAnimeLinksFromPages(int $from = 1, int $to = 9999): array
    {
        $ref = 'https://online.anidub.com/sf/page:1';
        $animes_refs = array();

        // получение номера последней страницы
        $lastPage = self::getLastPage($ref);

        // защита от перескока по кол-ву страниц
        if($from < 1) $from = 1;
        if ($to == null || $to > $lastPage) $to = $lastPage;

        // получение ссылок на страницы
        $pages = array();
        for ($page = $from; $page <= $to; $page++) {
            $pages[] = 'https://online.anidub.com/sf/page:' . $page;
        }

        /**
         * @var Response[] $fulfilled
         * @var ConnectException[] $rejected
         */
        $fulfilled =  $rejected = [];
        Requester::postGuzzleAsync($pages, AnidubSource::getLoginData(),$fulfilled,$rejected);

        foreach ($fulfilled as $url => $response) {

            $doc = new Document($response->getBody()->getContents());
            $animes_refs = array_merge($animes_refs, self::getAnimeLinksFromPage($doc));
        }

        // лог ошибок по неудачным запросам
        foreach ($rejected as $link => $exception) {

            Log::channel('parsing')->error("Неудачный запрос: $link || message: " . $exception->getMessage());
        }

        return $animes_refs;
    }

    public static function getAnimeLinksFromPage(Document $doc): array
    {
        $animes_refs = null;

        foreach ($doc->find('div.newstitle') as $newstitle) {

            $ref = $newstitle->first('a')->href;
            //фильтр на пустые ссылки, блоги, анонсы, новости
            $filter = $ref !== false &&
                !preg_match('!com/videoblog/!', $ref) &&
                !preg_match('!com/anons_ongoing/!', $ref) &&
                !preg_match('!com/anidub_news/!', $ref);

            if ($filter) $animes_refs[] = $ref;
        }

        if(!$animes_refs) Log::channel('parsing')->error($doc);

        return $animes_refs;
    }

    /**
     * Возвращает номер последней страницы каталога аниме
     *
     * @param string $ref Ссылка на страницу каталог аниме
     * @return int
     */
    private static function getLastPage(string $ref): int
    {
        $response = Requester::postGuzzle($ref, AnidubSource::getLoginData());
        $doc = new Document($response->getBody()->getContents());

        $lastPage = $doc->first('div.navi')->first('span.navigation')->lastChild()->text();

        return $lastPage;
    }

    function getCategories(Document $doc): array
    {
        $categories = $doc->first('div.newsinfo')->find('a');
        array_shift($categories); // убирает "Автор: kOsjaK"
        foreach ($categories as $key => &$category) {
            $category = $category->text();
        }
        return $categories;
    }

    function getName(Document $doc): string
    {
        $title = $doc->first('div.title')->first('h1.titlfull')->text();
        $title = explode(' / ', $title);

        return trim($title[0]);
    }

    function getOriginalName(Document $doc): ?string
    {
        $title = $doc->first('div.title')->first('h1.titlfull')->text();
        $title = explode(' / ', $title);

        if(!isset($title[1])) return null;

        preg_match('![a-z0-9]+[^\\[]+!ui', $title[1], $match);
        return trim($match[0]);
    }

    function getEpisodesReleased(Document $doc): ?string
    {
        $title = $doc->first('div.title')->first('h1.titlfull')->text();
        $title = explode(' / ', $title);

        if(!isset($title[1])) return null;

        preg_match('![0-9]+ из [0-9a-zа-я]+!ui', $title[1], $match);

        if (isset($match[0])) { // если существует
            return $match[0];
        } else {
            return '';
        }
    }

    function getYears(Document $doc): ?array
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Год: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return [];

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return explode(', ', $rowText);
    }

    function getGenres(Document $doc): array
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Жанр: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return [];

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return explode(', ', $rowText);
    }

    function getCountry(Document $doc): ?string
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Страна: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return null;

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return $rowText;
    }

    function getEpisodesCount(Document $doc): ?string
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Количество серий: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return null;

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return $rowText;
    }

    function getReleaseDateFrom(Document $doc): ?string
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Дата выпуска: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return null;

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $releaseDate = trim($rowText);

        $releaseDate = preg_replace('!c!ui', 'с', $releaseDate);
        $dates['from'] = null;
        $dates['to'] = null;

        $match = [];
        if (preg_match('/(?:с\s)([0-9]{2}\.[0-9]{2}\.[0-9]{4})/ui', $releaseDate,$match)) {
            return $this->getNormalDate($match[1]);
        } else if (preg_match('![0-9]{2}\.[0-9]{2}\.[0-9]{4}!ui', $releaseDate,$match)) {
            return $this->getNormalDate($match[0]);
        } else if (preg_match('!^[0-9]{1}\.[0-9]{2}\.[0-9]{4}$!ui', $releaseDate,$match)) {
            return $this->getNormalDate('0'.$match[0]);
        } else return null;
    }

    function getReleaseDateTo(Document $doc): ?string
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Дата выпуска: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return null;

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $releaseDate = trim($rowText);

        $releaseDate = preg_replace('!c!ui', 'с', $releaseDate);

        $match = [];
        if (preg_match('/(?:с\s)([0-9]{2}\.[0-9]{2}\.[0-9]{4})/ui', $releaseDate,$match)) {
            if (preg_match('/(?:по\s)([0-9]{2}\.[0-9]{2}\.[0-9]{4})/ui', $releaseDate,$match)) {
                return $this->getNormalDate($match[1]);
            } else {
                return null;
            }
        } elseif (preg_match('![0-9]{2}\.[0-9]{2}\.[0-9]{4}!ui', $releaseDate,$match)) {
            return $this->getNormalDate($match[0]);
        } else {
            return null;
        }
    }

    function getProducer(Document $doc): ?string
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Режиссер: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return null;

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return $rowText;
    }

    function getAuthor(Document $doc): ?string
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Автор оригинала / Сценарист: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return null;

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return $rowText;
    }

    function getDubbings(Document $doc): array
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Озвучивание: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return [];

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return explode(', ', $rowText);
    }

    function getTimings(Document $doc): array
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Тайминг и работа со звуком: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return [];

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return explode(', ', $rowText);
    }

    function getTranslates(Document $doc): array
    {
        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $searched = 'Перевод: ';

        $el = $info->xpath("(//li/b[text()='$searched'])[1]/..")[0] ?? null;
        if(!$el) return [];

        $rowText = $el->text();
        $rowText = explode(': ', $rowText)[1];
        $rowText = trim($rowText);

        return explode(', ', $rowText);
    }

    function getStudioName(Document $doc): ?string
    {
        $studio_name = null;

        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $video_info = $info->first('li.video_info');

        if($video_info) {
            $el_studio = $video_info->first('img');
            $studio_name = $el_studio->alt;
        }
        else return null;

        return $studio_name;
    }

    protected function getStudioPoster(Document $doc): ?string
    {
        $studio_poster = null;

        $info = $doc->first('#dle-content')->first('ul[class=reset]');
        $video_info = $info->first('li.video_info');

        if($video_info) {
            $el_studio = $video_info->first('img');
            $studio_poster = 'https://online.anidub.com'.$el_studio->src;
        }
        else return null;

        return $studio_poster;
    }

    function getPoster(Document $doc): string
    {
        $poster = $doc->first('div.poster_img img')->src;
        return $poster;
    }

    function getRating(Document $doc): ?float
    {
        $rating = $doc->first('#dle-content')->first('div.rate_view');

        if ($rating == null) return null;

        $rating = $rating->first('b')->text();
        if (!is_numeric($rating)) {
            return null;
        }

        return $this->modifyRating($rating);
    }

    function modifyRating(float $rating): ?float
    {
        if($rating === null) return null;
        return $rating * 2;
    }

    function getDescription(Document $doc): string
    {
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
    }

    function getEpisodeLinks(Document $doc): array
    {
        $episodesLinks = [];
        $videoElements = $this->getVideoElements($doc);

        if($videoElements !== null) {
            $episodesLinks = $this->getLinksFromVideoElements($videoElements, $this->sourceName);
        } else { // нет селекторов - фильм
            $episodesLinks = $this->getLinksFromIframe($doc, $this->sourceName);
        }

        foreach ($episodesLinks as $player => &$links) {
            foreach ($links as &$link) {
                $link['link'] = $this->normalizeLinkProtocol($link['link']);
            }
        }

        return $episodesLinks;
    }

    /**
     * Исправляет опечатки в протоколе ссылки
     *
     * @param string $link
     * @return string
     */
    private function normalizeLinkProtocol(string $link): string
    {
        if(preg_match('@^//@ui', $link)) {
            return 'https:'.$link;
        } elseif (preg_match('@^http:/(?!/)@ui', $link)) {
            return preg_replace('@^http:/(?!/)@ui', 'http://', $link);
        }


        return $link;
    }

    /**
     * Возвращает видео элементы, если они есть
     *
     * @param Document $doc
     * @return array|null
     */
    private function getVideoElements(Document $doc): ?array
    {
        $elements = null;
        $playerSelectors = [
            'sel',
            'sel2',
            'sel3',
        ];

        foreach ($playerSelectors as $playerSelector) {
            $el_selector = $doc->first("#$playerSelector");
            if ($el_selector == null) continue;

            $elements[$playerSelector] = $el_selector->find('option');
        }

        return $elements;
    }

    /**
     * Возвращает сслыки на видео из селекторов плееров
     *
     * @param array $videoElements
     * @param $sourceName
     * @return array
     * @throws \Exception
     */
    private function getLinksFromVideoElements(array $videoElements, string $sourceName): array
    {
        $links = null;

        foreach ($videoElements as $selector => $el_videos) {
            foreach ($el_videos as $el_video) {

                $video_id = $this->getVideoId($el_video->value);
                $videoLink = $this->getVideoLink($el_video->value);
                $player = $this->getPlayerName($videoLink);

                // разные серии имеют одну ссылку
                if(!$this->checkLinkUnique($links, $videoLink, $player, $sourceName)) continue;
                else {
                    if($selector === 'sel2') { // особое поведение для anidub плеера
                        $videoLink = $this->getAnidubVideoLink($videoLink, $sourceName);
                        if (!$videoLink) continue;
                    }
                    $links[$player][$video_id]['link'] = trim($videoLink);
                    $links[$player][$video_id]['text'] = $el_video->text();
                }
            }
        }

        return $links;
    }

    /**
     * Возвращает ссылку с ifram'а - для фильмов и ova
     *
     * @param Document $doc
     * @param string $sourceName
     * @return string[]
     * @throws \Exception
     */
    private function getLinksFromIframe(Document $doc, string $sourceName): array
    {
        $src = $doc->first('#vk1')->first('iframe')->src;
        $src = trim($src);

        if($src != '') {
            $player = $this->getPlayerName($src);
            return [ $player => [ 1 => ['link' => $src, 'text' => null]] ];
        } else {
            throw new \Exception('нет ссылки на серию в iframe');
        }
    }

    /**
     * Проверяет ссылки на уникальнось в пределах данного аниме
     *
     * @param array $links
     * @param string $videoLink
     * @param string $player
     * @param string $sourceName
     * @return bool
     */
    private function checkLinkUnique(?array $links, string $videoLink, string $player, string $sourceName): bool
    {
        if(isset($links[$player]) && in_array($videoLink, array_column($links[$player], 'link'))) {
            Log::channel('parsing')->info("Разные серии имеют одну ссылку. || Source: $sourceName || Плеер: $player || Ссылка на видео: $videoLink");
            return false;
        }
        return true;
    }

    /**
     * Отделяет ссылку на видео от номера видео
     *
     * @param string $videoLink
     * @return string
     */
    private function getVideoLink(string $videoLink): string
    {
        $match = null;
        preg_match('@.+(?=\|)@ui', $videoLink, $match);
        return $match[0];
    }

    /**
     * Преобразует ссылку на плеере анидаба в рабочий вид
     *
     * @param string $videoLink
     * @param $sourceName
     * @return null|string
     */
    private function getAnidubVideoLink(string &$videoLink, $sourceName): ?string
    {
        $sub_link = null;
        if (!preg_match('@(?<=player/index\.php\?vid=)[^\s]*\.mp4(?=&url=)@ui', $videoLink,$sub_link)) {
            Log::channel('parsing')->info("Не удалось вытянуть из анидаб плеера прямую ссылку на серию || Source: $sourceName || link: $videoLink" );
            return null;
        }
        return "https://cdn4.anivid.nut.cc/vid$sub_link[0]";
    }

    /**
     * Возвращает номер серии
     *
     * @param string $videoLink
     * @return int
     */
    private function getVideoId(string $videoLink): int
    {
        $match = null;
        preg_match('@(?<=\|)[0-9]+@ui', $videoLink, $match);
        return $match[0];
    }

    /**
     * Возвращает имя плеера из ссылки на видео
     *
     * @param string $videoLink
     * @return string
     * @throws \Exception
     */
    private function getPlayerName(string $videoLink): string
    {
        try {
            $match = null;
            preg_match('@(https{0,1}://|//|/)(.*?\.)?(.+?)(\..*)(/)@ui', $videoLink, $match);
            return $match[3];
        } catch (\Throwable $exception) {
            throw new \Exception("не удалось вытянуть название плеера из ссылки: $videoLink");
        }
    }

    function getEpisodesList(Document $doc): ?array
    {
        /** @todo Получение списка эпизодов */
        try {
            /*$episodes = $doc->first('div[style=display:inline;]')->find('span');
            if ($episodes) {
                $episodes = $episodes->text();
                $episodes = explode("\r\n", $episodes);
                $returnArray['Эпизоды'] = $episodes;
            }*/

        } catch (\Throwable $exception) {
            Log::channel('parsing')->info('Не удалось получить список эпизодов: ' . $this->sourceName);
            return null;
        }

        return null;
    }

    function getOtherParts(Document $doc): array
    {
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
            Log::channel('parsing')->info('Не удалось получить другие части аниме: ' . $this->sourceName);
            return [];
        }
    }

    /**
     * Приводит дату к нужному формату для записи в бд
     *
     * @param string $date
     * @return null|string
     */
    private function getNormalDate(string $date): ?string
    {
        $dd = null;
        $mm = null;
        $yy = null;

        preg_match('@^[0-9]{2}@ui', $date, $dd);
        preg_match('@(?:\.)([0-9]{2})(?:\.)@ui', $date, $mm);
        preg_match('@[0-9]{4}$@ui', $date, $yy);

        return $yy[0].'.'.$mm[1].'.'.$dd[0];
    }

    public static function parseAll(int $inRow = 300): array
    {
        $animes = [];

        $animeLinks = static::getAnimeLinksFromPages();
        dump('все ссылки на аниме получены');

        $chunks = array_chunk($animeLinks, $inRow);

        $parser = new static();

        foreach ($chunks as $key => $chunk) {
            $sources = AnidubSource::spawnMany($chunk);
            $animes = array_merge($animes, $parser->feedMany($sources));
            dump(($key+1) * $inRow . ' из ' . count($chunks) * $inRow);
        }

        return $animes;
    }
}