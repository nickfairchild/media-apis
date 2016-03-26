<?php

namespace Nick\Media\TVDB;

use Carbon\Carbon;

class Series
{
    public $id;
    public $language;
    public $name;
    public $banner;
    public $overview;
    public $firstAired;
    public $imdbId;
    public $actors     = [];
    public $airsDayOfWeek;
    public $airsTime;
    public $contentRating;
    public $genres     = [];
    public $network;
    public $rating;
    public $ratingCount;
    public $runtime;
    public $status;
    public $added;
    public $fanArt;
    public $lastUpdated;
    public $poster;
    public $zap2itId;
    public $aliasNames = [];

    /**
     * Series constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->id            = (int)$data->id;
        $this->language      = (string)$data->Language;
        $this->name          = (string)$data->SeriesName;
        $this->banner        = (string)$data->banner;
        $this->overview      = (string)$data->Overview;
        $this->firstAired    = (string)$data->FirstAired !== '' ? Carbon::createFromFormat('Y-m-d', (string)
        $data->FirstAired)->toDateString() : null;
        $this->imdbId        = (string)$data->IMDB_ID;
        if (isset($data->Actors)) {
            $this->actors = (array)TVDB::removeEmptyIndexes(explode('|', (string)$data->Actors));
        }
        $this->airsDayOfWeek = (string)$data->Airs_DayOfWeek;
        $this->airsTime      = (string)$data->Airs_Time;
        $this->contentRating = (string)$data->ContentRating;
        if (isset($data->Genre)) {
            $this->genres = (array)TVDB::removeEmptyIndexes(explode('|', (string)$data->Genre));
        }
        $this->network       = (string)$data->Network;
        $this->rating        = (float)$data->Rating;
        $this->ratingCount   = (int)$data->RatingCount;
        $this->runtime       = (int)$data->Runtime;
        $this->status        = (string)$data->Status;
        if (isset($data->added)) {
//            $this->added = Carbon::createFromFormat('Y-m-d H:i:s', (string)$data->added)->toDateTimeString();
        }
        $this->fanArt        = (string)$data->fanart;
        $this->lastUpdated   = Carbon::createFromTimestamp((int)$data->lastupdated)->toDateTimeString();
        $this->poster        = (string)$data->poster;
        $this->zap2itId      = (string)$data->zap2it_id;
        if (isset($data->AliasNames)) {
            $this->aliasNames = (array)TVDB::removeEmptyIndexes(explode('|', (string)$data->AliasNames));
        }
    }
}