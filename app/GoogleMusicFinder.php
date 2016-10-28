<?php

namespace App;

use CurlHelper;
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
        $key_terms = array_merge($key_terms, explode(' ', $info->title));

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
        $title = $el_title->attr('title');

        // Get Artist
        $el_artist = $search_result->filter('a.subtitle');
        $artist = $el_artist->attr('title');

        $music_type = $music_info[0];
        if($music_type == 'song')
        {
            $music_type = 'track';
        }

        $id = $music_info[1];

        $link = sprintf('https://play.google.com/music/m/%s?t=%s_-_%s',
            $id,
            str_replace(' ', '_', $title),
            str_replace(' ', '_', $artist));

        $info = new MusicInfo;

        $info->fill([
            'id' => $music_info[1],
            'title' => $title,
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
        $search_result = $crawler->filter('#main-content-container');


        // Get image link
        $attrib_image = $crawler->filterXpath("//meta[@property='og:image']")->extract(['content']);
        $image = head($attrib_image);

        $image = $this->cleanseImageUrl($image);

        // Get track name
        $el_track = $search_result->filter('.title > a');
        $title = $el_track->text();

        // Get artist name
        $el_artist = $search_result->filter('.album-artist > a');
        $artist = $el_artist->text();

        // Get Album/Track from tracklist title
        $el_tracklist_title = $search_result->filter('.info-text > .title > a');
        $tracklist_title = $el_tracklist_title->text();

        if(strpos($tracklist_title, 'From the album') !== false)
        {
            $type = 'track';
        }
        else
        {
            $type = 'album';
        }

        $link = sprintf('https://play.google.com/music/m/%s?t=%s_-_%s',
            $id,
            str_replace(' ', '_', $title),
            str_replace(' ', '_', $artist));

        return [
            'id' => $id,
            'title' => $title,
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
