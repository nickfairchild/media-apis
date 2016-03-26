<?php

namespace Nick\Media\TVDB;

use GuzzleHttp\Client;

class TVDB
{
    const MIRROR_TYPE_XML    = 1;
    const MIRROR_TYPE_BANNER = 2;
    const MIRROR_TYPE_ZIP    = 4;

    const DEFAULT_LANGUAGE = 'en';

    protected $baseUrl         = '';
    protected $apiKey          = '';
    protected $mirrors         = [];
    protected $languages       = [];
    protected $defaultLanguage = 'en';
    protected $httpClient;

    public function __construct($baseUrl, $apiKey)
    {
        $this->baseUrl    = $baseUrl;
        $this->apiKey     = $apiKey;
        $this->httpClient = new Client();
    }

    public function getLanguage($abbreviation)
    {
        if (empty($this->languages)) {
            $this->getLanguages();
        }
        if (!isset($this->languages[$abbreviation])) {
            throw new \Exception('This language is not available.');
        }

        return $this->languages[$abbreviation];
    }

    public function setDefaultLanguage($language)
    {
        $this->defaultLanguage = $language;
    }

    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    public function getServerTime()
    {
        return (string)$this->fetchXml('Updates.php?type=none')->Time;
    }

    public function getMirror($typeMask = self::MIRROR_TYPE_XML)
    {
        if (empty($this->mirrors)) {
            $this->getMirrors();
        }

        return $this->mirrors[$typeMask][array_rand($this->mirrors[$typeMask], 1)];
    }

    public function getShows($name, $language = null)
    {
        $language = $language ?: $this->defaultLanguage;

        $data   = $this->fetchXml('GetSeries.php?seriesname=' . urlencode($name) . '&language=' . $language);
        $series = [];
        foreach ($data->Series as $show) {
            $series[] = new Series($show);
        }

        return $series;
    }

    public function getSeries($id, $language = null)
    {
        $language = $language ?: $this->defaultLanguage;

        $data = $this->fetchXml('series/' . $id . '/' . $language . '.xml');

        $series = new Series($data->Series);

        return $series;
    }

    public function getSeriesByRemoteId($id, $language = null)
    {
        $language = $language ?: $this->defaultLanguage;

        $data = $this->fetchXml('GetSeriesByRemoteID.php?' . $id . '&language=' . $language);

        return new Series($data->Series);
    }

    public function getBanners($seriesId)
    {
        $data = $this->fetchXml('series/' . $seriesId . '/banners.xml');

        $banners = [];
        foreach ($data->Banner as $banner) {
            $banners[] = new Banner($banner);
        }

        return $banners;
    }

    public function getActors($seriesId)
    {
        $data = $this->fetchXml('series/' . $seriesId . '/actors.xml');

        $actors = [];
        foreach ($data->Actor as $actor) {
            $actors[] = new Actor($actor);
        }

        return $actors;
    }

    public function getSeriesEpisodes($id, $language = null, $format = 'xml')
    {
        $language = $language ?: $this->defaultLanguage;

        switch ($format) {
            case 'xml' :
                $data = $this->fetchXml('series/' . $id . '/all/' . $language . '.' . $format);
                break;
            case 'zip' :
                if (!in_array('zip', stream_get_wrappers())) {
                    throw new \Exception('Your version of PHP does not support ZIP stream wrapper.');
                }
                $data = $this->fetchZip('series/' . $id . '/all/' . $language . '.' . $format, [], 'get', $language .
                    '.xml');
                break;
            default :
                throw new \Exception('Unsupported format.');
                break;

        }

        $series   = new Series($data->Series);
        $episodes = [];
        foreach ($data->Episode as $episode) {
            $episodes[(int)$episode->id] = new Episode($episode);
        }

        return ['series' => $series, 'episode' => $episodes];
    }

    public function getEpisode($seriesId, $season, $episode, $language = null)
    {
        $language = $language ?: $this->defaultLanguage;

        $data = $this->fetchXml('series/' . $seriesId . '/default/' . $season . '/' . $episode . '/' . $language . '
        .xml');

        return new Episode($data->Episode);
    }

    public function getEpisodeById($id, $language = null)
    {
        $language = $language ?: $this->defaultLanguage;

        $data = $this->fetchXml('episode/' . $id . '/' . $language . '.xml');

        return new Episode($data->Episode);
    }

    public function getUpdates($previousTime)
    {
        $data = $this->fetchXml('Updates.php?type=all&time=' . $previousTime);

        $series = [];
        foreach ($data->Series as $seriesId) {
            $series[] = (int)$seriesId;
        }
        $episodes = [];
        foreach ($data->Episode as $episodeId) {
            $episodes[] = (int)$episodeId;
        }

        return ['series' => $series, 'episodes' => $episodes];
    }

    public function fetchBanner($banner)
    {
        $url = $this->getMirror(self::MIRROR_TYPE_BANNER) . '/banners/' . $banner;

        return $this->httpClient->request('get', $url);
    }

    private function getLanguages()
    {
        $languages = $this->fetchXml('languages.xml');

        foreach ($languages as $language) {
            $this->languages[(string)$language->abbreviation] = [
                'name'         => (string)$language->name,
                'abbreviation' => (string)$language->abbreviation,
                'id'           => (int)$language->id
            ];
        }
    }

    private function fetchXml($function, $params = [], $method = 'get')
    {
        if (strpos($function, '.php') > 0) {
            $url = $this->getMirror(self::MIRROR_TYPE_XML) . '/api/' . $function;
        } else {
            $url = $this->getMirror(self::MIRROR_TYPE_XML) . '/api/' . $this->apiKey . '/' . $function;
        }

        $data = $this->httpClient->request($method, $url, $params);

        $xml = $this->getXml($data);

        return $xml;
    }

    private function fetchZip($function, $params = [], $method = 'get', $file = null)
    {
        if (strpos($function, '.php') > 0) {
            $url = $this->getMirror(self::MIRROR_TYPE_XML) . '/api/' . $function;
        } else {
            $url = $this->getMirror(self::MIRROR_TYPE_XML) . '/api/' . $this->apiKey . '/' . $function;
        }

        $zipName = tempnam(sys_get_temp_dir(), 'tvdb-');
        $zip     = fopen($zipName, 'w');
        fwrite($zip, $this->httpClient->request($method, $url, $params));
        fclose($zip);
        if (is_null($file)) {
            $file = $this->getDefaultLanguage() . '.xml';
        }
        $dataPath = "zip://" . $zipName . "#" . $file;
        $data     = file_get_contents($dataPath);

        $xml = $this->getXml($data);

        return $xml;
    }

    private function getMirrors()
    {
        $data    = $this->httpClient->request('get', $this->baseUrl . '/api/' . $this->apiKey . '/mirrors.xml');
        $mirrors = $this->getXml($data);

        foreach ($mirrors as $mirror) {
            $typeMask   = (int)$mirror->typemask;
            $mirrorPath = (string)$mirror->mirrorpath;

            if ($typeMask & self::MIRROR_TYPE_XML) {
                $this->mirrors[self::MIRROR_TYPE_XML][] = $mirrorPath;
            }
            if ($typeMask & self::MIRROR_TYPE_BANNER) {
                $this->mirrors[self::MIRROR_TYPE_BANNER][] = $mirrorPath;
            }
            if ($typeMask & self::MIRROR_TYPE_ZIP) {
                $this->mirrors[self::MIRROR_TYPE_ZIP][] = $mirrorPath;
            }
        }
    }

    private function getXml($data)
    {
        $simpleXml = new \SimpleXMLElement($data->getBody()->getContents());
        if (!$simpleXml) {
            throw new \Exception('XML file could not be loaded.');
        }

        return $simpleXml;
    }

    public static function removeEmptyIndexes($array)
    {
        $length = count($array);

        for ($i = 0; $i <= $length - 1; $i++) {
            if (trim($array[$i]) == '') {
                unset($array[$i]);
            }
        }

        sort($array);

        return $array;
    }
}