<?php

namespace App;

use CurlHelper;
use InvalidArgumentException;
use App\Contracts\MusicService;
use Symfony\Component\DomCrawler\Crawler;

class GoogleMusicFinder implements MusicService
{
    /**
     * Validates Google Music service URIs
     *
     * @param  string  $uri
     *
     * @return  bool
     */
    public function matches($uri)
    {
        return strpos($uri, 'play.google.com') !== false;
    }

    /**
     * Takes a resource URI and returns the resource type and ID, or
     * false if the URI is not valid.
     *
     * @param  string  $uri
     *
     * @return  string[ resource type, resource id ]|bool
     */
    public function music_id($uri)
    {
        $is_match = preg_match("/play.google.com\/music\/m\/(\w+)/", $uri, $matches);

        if(! $is_match) {
            $is_match = preg_match("/play\.google\.com\/music\/listen\?.*album\/(\w+)/", $uri, $matches);
        }

        if($is_match === 1) {
            return ['track', $matches[1]];
        }

        return false;
    }

    public function music_info_by_id($type, $id)
    {
        $info = new MusicInfo;

        $music_info = $this->fetchMusicInfo($id);
        $info->fill($music_info);

        return $info;
    }

    public function music_info($uri)
    {
        $is_match = preg_match("/play.google.com\/music\/m\/(\w+)/", $uri, $matches);

        if(! $is_match) {
            $is_match = preg_match("/play\.google\.com\/music\/listen\?.*album\/(\w+)/", $uri, $matches);
        }

        if($is_match === 1) {
            $info = new MusicInfo;

            $music_info = $this->fetchMusicInfo($matches[1]);
            $info->fill($music_info);

            return $info;
        }
    }

    public function search(MusicInfo $info)
    {
        $key_terms = [];
        $key_terms = array_merge($key_terms, explode(' ', $info->artist));
        $key_terms = array_merge($key_terms, explode(' ', $info->album));

        if($info->track) {
            $key_terms = array_merge($key_terms, explode(' ', $info->track));
        } else {
            array_push($key_terms, 'album');
        }

        $uri = sprintf('https://play.google.com/store/search?q=%s&c=music', implode('%20', $key_terms));

        $response = CurlHelper::factory($uri)->exec();

        $response_body = $response['content'];

        // Initialize crawler
        $crawler = new Crawler($response_body);

        // Get Music ID
        $search_result = $crawler->filter('[data-docid]');
        $music_info = explode('-', $search_result->attr('data-docid'));

        // Get Album Art
        $el_image = $search_result->filter('img.cover-image');
        $image = $el_image->attr('data-cover-large');

        // request jpeg instead of webm image
        $image = $this->cleanseImageUrl($image);

        // Get Title
        $el_title = $search_result->filter('a.title');
        $album = $el_title->attr('title');

        // Get Artist
        $el_artist = $search_result->filter('a.subtitle');
        $artist = $el_artist->attr('title');

        $music_type = $music_info[0];
        if($music_type == 'song')
        {
            $music_type = 'track';
        }

        $id = $music_info[1];

        $link = sprintf('https://play.google.com/music/m/%s', $id);

        $info = new MusicInfo;

        $info->fill([
            'id' => $music_info[1],
            'album' => $album,
            'track' => $album,
            'artist' => $artist,
            'type' => $music_type,
            'link' => $link,
            'image_link' => $image,
        ]);

        return $info;
    }

    /**
     * @param $id
     * @return array
     */
    protected function fetchMusicInfo($id)
    {
        $uri = sprintf("https://play.google.com/music/preview/%s", $id);

        $response = CurlHelper::factory($uri)
            ->setGetParams([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
            ])
            ->exec();

        $response_body = $response['content'];

        // Initialize crawler
        $crawler = new Crawler($response_body);

        try {
            $track = $crawler->filter('body > [itemscope][itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] > [itemprop="name"]')->text();
            $artist = $crawler->filter('body > [itemscope][itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] [itemprop="byArtist"]')->text();
            $album = $crawler->filter('body > [itemscope][itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] [itemprop="inAlbum"]')->text();
            $image = $crawler->filter('body > [itemtype="http://schema.org/MusicRecording/PlayMusicTrack"] [itemprop="image"]')->attr('href');

            $type = 'track';
        } catch(InvalidArgumentException $e) {
            // Album was shared
            $track = null;
            $album = $crawler->filter('body > [itemtype="http://schema.org/MusicAlbum/PlayMusicAlbum"] [itemprop="name"]')->text();
            $artist = $crawler->filter('body > [itemtype="http://schema.org/MusicAlbum/PlayMusicAlbum"] [itemprop="byArtist"]')->text();
            $image = $crawler->filter('body > [itemtype="http://schema.org/MusicAlbum/PlayMusicAlbum"] [itemprop="image"]')->attr('href');

            $type = 'album';
        }

        $link = sprintf('https://play.google.com/music/m/%s', $id);

        return [
            'id' => $id,
            'album' => $album,
            'track' => $track,
            'type' => $type,
            'artist' => $artist,
            'link' => $link,
            'image_link' => $image,
        ];
    }

    protected function cleanseImageUrl($image)
    {
        return str_replace('=w340-rw', '=w340', $image);
    }
}
