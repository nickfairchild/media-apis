<?php

namespace Media\TVDB;

use Carbon\Carbon;

class Episode
{
    public $id;
    public $number;
    public $season;
    public $directors = [];
    public $guestStars = [];
    public $writers = [];
    public $name;
    public $firstAired;
    public $imdbId;
    public $language;
    public $overview;
    public $rating;
    public $ratingCount;
    public $lastUpdated;
    public $seasonId;
    public $seriesId;
    public $thumbnail;

    /**
     * Episode constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->id = (int)$data->id;
        $this->number = (int)$data->EpisodeNumber;
        $this->season = (int)$data->SeasonNumber;
        $this->directors = (array)TVDB::removeEmptyIndexes(explode('|', (string)$data->Director));
        $this->name = (string)$data->EpisodeName;
        $this->firstAired = (string)$data->FirstAired !== '' ? Carbon::createFromFormat('Y-m-d', (string)
        $data->FirstAired)->toDateString() : null;
        $this->guestStars = (array)TVDB::removeEmptyIndexes(explode('|', (string)$data->GuestStars));
        $this->imdbId = (string)$data->IMDB_ID;
        $this->language = (string)$data->Language;
        $this->overview = (string)$data->Overview;
        $this->rating = (float)$data->Rating;
        $this->ratingCount = (int)$data->RatingCount;
        $this->lastUpdated = Carbon::createFromTimestamp((int)$data->lastupdated)->toDateTimeString();
        $this->writers = (array)TVDB::removeEmptyIndexes(explode('|', (string)$data->Writer));
        $this->thumbnail = (string)$data->filename;
        $this->seasonId = (int)$data->seasonid;
        $this->seriesId = (int)$data->seriesid;
    }
}