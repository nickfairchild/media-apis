<?php

namespace Nick\Media\Fanart;

use GuzzleHttp\Client;

class Fanart
{
    private $baseurl = 'http://webservice.fanart.tv/v3';
    private $apikey;

    public function __construct($apikey)
    {
        $this->apikey     = $apikey;
        $this->httpClient = new Client();
    }

    public function getShowImages($id, $type)
    {
        $data = $this->fetchJson($id, 'tv');

        $images = [];
        foreach ($data[$type] as $image) {
            $images[] = new Image($image);
        }

        return $images;
    }

    public function getMovieImages($id, $type)
    {
        $data = $this->fetchJson($id, 'movies');

        $images = [];
        foreach ($data[$type] as $image) {
            $images[] = new Image($image);
        }

        return $images;
    }

    private function fetchJson($id, $type)
    {
        $url = $this->baseurl . '/' . $type . '/' . $id . '?api_key=' . $this->apikey;

        $data = $this->httpClient->request('get', $url);
        $json = $this->getJson($data);

        return $json;
    }

    private function getJson($data)
    {
        $json = json_decode($data->getBody()->getContents(), true);

        return $json;
    }
}