<?php

namespace Library\AnimeParser;


class ParsedAnime
{
    public $categories = [];
    public $name = null;
    public $originalName = null;
    public $episodesReleased = null;
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
    public $studioName = null;
    public $studioPoster = null;
    public $poster = null;
    public $rating = null;
    public $description = null;
    public $episodesList = null;
    public $episodeLinks = [];
    public $otherParts = [];
    public $sourceLink = null;
}