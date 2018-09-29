<?php

namespace Library\AnimeParser;

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

abstract class AnimeParser
{
    protected $sourceClassName = null; // имя класса-источника который принимает данный класс парсера
    protected $sourceName = null; // имя источника на парсинге в данный момент

    /**
     * Принимает источник и парсит аниме
     *
     * @param AnimeSource $source
     * @return ParsedAnime
     * @throws \Exception
     */
    final public function feed(AnimeSource $source) : ParsedAnime
    {
        $this->checkSourceClass($source, $this->sourceClassName);

        return $this->parse($source->getData(), $source->getSourceName());
    }

    /**
     * Принимает массив источников, возвращает массив распаршеного аниме
     *
     * @param AnimeSource[] $sources
     * @return ParsedAnime[]
     * @throws \Exception
     */
    final public  function feedMany(array $sources) : array
    {
        $dataArr = [];
        foreach ($sources as $source) {
            $this->checkSourceClass($source, $this->sourceClassName);

            $dataArr[] = $this->parse($source->getData(), $source->getSourceName());
        }

        return $dataArr;
    }

    /**
     * Парсинг документа
     *
     * @param Document $doc
     * @param string $sourceName
     * @return ParsedAnime
     */
    final protected function parse(Document $doc, string $sourceName) : ParsedAnime
    {
        $parsed = new ParsedAnime();
        $this->sourceName = $sourceName;

        try {
            $parsed->categories = $this->getCategories($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить категории аниме: $sourceName" . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->name = $this->getName($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить имя аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->originalName = $this->getOriginalName($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить оригинальное имя аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->episodesReleased = $this->getEpisodesReleased($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить EpisodesReleased аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->years = $this->getYears($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить год аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->genres = $this->getGenres($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить жанры аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->country = $this->getCountry($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить страну аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->episodesCount = $this->getEpisodesCount($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить кол-во серий аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->releaseDateFrom = $this->getReleaseDateFrom($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить дату начала выпуска аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->releaseDateTo = $this->getReleaseDateTo($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить дату конца выпуска аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->producer = $this->getProducer($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить режиссёра аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->author = $this->getAuthor($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить автора аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->dubbings = $this->getDubbings($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить озвучивающих аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->timings = $this->getTimings($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить работающих с таймигом аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->translates = $this->getTranslates($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить переводящих аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->studioName = $this->getStudioName($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить студию аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->studioPoster = $this->getStudioPoster($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить постер студии аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->poster = $this->getPoster($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить постер аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->rating = $this->getRating($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить рейтинг аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->description = $this->getDescription($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить описание аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->episodeLinks = $this->getEpisodeLinks($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить ссылки на серии аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->episodesList = $this->getEpisodesList($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить список эпизодов аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        try {
            $parsed->otherParts = $this->getOtherParts($doc);
        } catch (\Throwable $exception) {
            Log::channel('parsing')->info("Не удалось получить другие части аниме: $sourceName || message: " . $exception->getMessage() . ' || line: ' . $exception->getLine() . ' || file: ' . $exception->getFile());
        }

        $parsed->sourceLink = $sourceName;

        return $parsed;
    }

    /**
     * Проверяет соответствие выбранного класса трубуемому классу - источнику
     *
     * @param AnimeSource $from
     * @param $to
     * @throws \Exception
     */
    final protected function checkSourceClass(AnimeSource $from, $to)
    {
        if(!($from instanceof $to)) {
            throw new \Exception('Ожидался класс ' . $to);
        }
    }

    abstract protected function getCategories(Document $doc): array;

    abstract protected function getName(Document $doc): string ;

    abstract protected function getOriginalName(Document $doc): ?string;

    abstract protected function getEpisodesReleased(Document $doc): ?string;

    abstract protected function getYears(Document $doc): ?array;

    abstract protected function getGenres(Document $doc): array;

    abstract protected function getCountry(Document $doc): ?string;

    abstract protected function getEpisodesCount(Document $doc): ?string;

    abstract protected function getReleaseDateFrom(Document $doc): ?string;

    abstract protected function getReleaseDateTo(Document $doc): ?string;

    abstract protected function getProducer(Document $doc): ?string;

    abstract protected function getAuthor(Document $doc): ?string;

    abstract protected function getDubbings(Document $doc): array;

    abstract protected function getTimings(Document $doc): array;

    abstract protected function getTranslates(Document $doc): array;

    abstract protected function getStudioName(Document $doc): ?string;

    abstract protected function getStudioPoster(Document $doc): ?string;

    abstract protected function getPoster(Document $doc): string;

    abstract protected function getRating(Document $doc): ?float;

    abstract protected function getDescription(Document $doc): string;

    abstract protected function getEpisodeLinks(Document $doc): array;

    abstract protected function getEpisodesList(Document $doc): ?array;

    abstract protected function getOtherParts(Document $doc): array;

    /**
     * Получить ссылки на аниме в выбранном диапазоне страниц каталога
     *
     * @param int $from
     * @param int $to
     * @return string[]
     */
    abstract public static function getAnimeLinksFromPages(int $from = 1, int $to = 9999): array;

    /**
     * Спарсить все аниме на сайте
     *
     * @param int $inRow Колличество запросов за раз
     * @return ParsedAnime[]
     */
    abstract public static function parseAll(int $inRow = 300): array;

}