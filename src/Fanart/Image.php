<?php

namespace Nick\Media\Fanart;

class Image
{
    public $id;
    public $url;
    public $likes;
    public $season;

    public function __construct($data)
    {
        $this->id    = $data['id'];
        $this->url   = $data['url'];
        $this->likes = $data['likes'];
        if (isset($data['season'])) {
            $this->season = $data['season'];
        }
    }
}