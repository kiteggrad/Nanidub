<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Library\AnimeParser\ParsedAnime;

class Anime extends Model
{
    public static function saveParsed(ParsedAnime $parsedAnime): ?self
    {
        DB::beginTransaction();
        try {

            // аниме
            $m_anime = new Anime();
            $m_anime->name = $parsedAnime->name;
            $m_anime->originalName = $parsedAnime->originalName;
            $m_anime->episodesReleased = $parsedAnime->episodesReleased;
            $m_anime->episodesCount = $parsedAnime->episodesCount;
            $m_anime->episodesList = $parsedAnime->episodesList;
            $m_anime->releaseDateFrom = $parsedAnime->releaseDateFrom;
            $m_anime->releaseDateTo = $parsedAnime->releaseDateTo;
            $m_anime->poster = $parsedAnime->poster;
            $m_anime->rating = $parsedAnime->rating;
            $m_anime->description = $parsedAnime->description;
            if ($parsedAnime->country)
                $m_anime->country_id = Country::firstOrCreate(['country' => $parsedAnime->country])->id;
            else
                $m_anime->country_id = null;
            if ($parsedAnime->producer)
                $m_anime->producer_id = Producer::firstOrCreate(['producer' => $parsedAnime->producer])->id;
            else
                $m_anime->producer_id = null;
            if ($parsedAnime->author)
                $m_anime->author_id = Author::firstOrCreate(['author' => $parsedAnime->author])->id;
            else
                $m_anime->author_id = null;
            if ($parsedAnime->studioName)
                $m_anime->studio_id = Studio::firstOrCreate(['studio' => $parsedAnime->studioName], ['poster' => $parsedAnime->studioPoster])->id;
            else
                $m_anime->studio_id = null;
            $m_anime->sourceLink = $parsedAnime->sourceLink;
            $m_anime->slug = str_random();
            $m_anime->save();
            $m_anime->slug = self::generateSlug([$m_anime->id, $parsedAnime->name, $parsedAnime->originalName, $parsedAnime->years[0] ?? '']);
            $m_anime->save();

            $anime_id = $m_anime->id;

            // годы
            $years = [];
            foreach ($parsedAnime->years as $year) {
                $years[] = Year::firstOrCreate(['year' => $year]);
            }
            $m_anime->years()->saveMany($years);

            // категории
            $categories = [];
            foreach ($parsedAnime->categories as $category) {
                $categories[] = Category::firstOrCreate(['category' => $category]);
            }
            $m_anime->categories()->saveMany($categories);

            // озвучивание
            $dubbings = [];
            foreach ($parsedAnime->dubbings as $dubbing) {
                $dubbings[] = Dubbing::firstOrCreate(['dubbing' => $dubbing]);
            }
            $m_anime->dubbings()->saveMany($dubbings);

            // тайминг и работа со звуком
            $timings = [];
            foreach ($parsedAnime->timings as $timing) {
                $timings[] = Timing::firstOrCreate(['timing' => $timing]);
            }
            $m_anime->timings()->saveMany($timings);

            // жанры
            $genres = [];
            foreach ($parsedAnime->genres as $genre) {
                $genres[] = Genre::firstOrCreate(['genre' => $genre]);
            }
            $m_anime->genres()->saveMany($genres);

            // перевод
            $translates = [];
            foreach ($parsedAnime->translates as $translate) {
                $translates[] = Translate::firstOrCreate(['translate' => $translate]);
            }
            $m_anime->translates()->saveMany($translates);

            // ссылки на серии
            $episodeLinks = [];
            foreach ($parsedAnime->episodeLinks as $player => $episodes) {
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
            foreach ($parsedAnime->otherParts as $number => $otherPart) {

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

            return $m_anime;

        } catch (\Throwable $exception) {
            Log::channel('parsing')->error("Не удалось добавить аниме в бд: " . $parsedAnime->sourceLink . ' ' .
                'message: ' . $exception->getMessage() . ' ' .
                'line: ' . $exception->getLine()
            );
            DB::rollBack();

            return null;
        }
    }

    public static function generateSlug(array $elements)
    {
        $slug = '';
        $string ='';
        foreach ($elements as $element) {
            $string .= ' ' . $element;
        }
        $slug = str_slug($string);
        return $slug;
    }

    public function country()
    {
        return $this->belongsTo('App\country');
    }

    public function producer()
    {
        return $this->belongsTo('App\producer');
    }

    public function author()
    {
        return $this->belongsTo('App\author');
    }

    public function studio()
    {
        return $this->belongsTo('App\studio');
    }

    public function categories()
    {
        return $this->belongsToMany('App\category');
    }

    public function years()
    {
        return $this->belongsToMany('App\year');
    }

    public function genres()
    {
        return $this->belongsToMany('App\genre');
    }

    public function dubbings()
    {
        return $this->belongsToMany('App\dubbing');
    }

    public function timings()
    {
        return $this->belongsToMany('App\timing');
    }

    public function translates()
    {
        return $this->belongsToMany('App\translate');
    }

    public function episodeLinks()
    {
        return $this->hasMany('App\EpisodeLink');
    }

    public function otherParts()
    {
        return $this->hasMany('App\OtherPart');
    }
}