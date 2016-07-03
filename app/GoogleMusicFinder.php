<?php

namespace App;

use CurlHelper;
use Symfony\Component\DomCrawler\Crawler;

class GoogleMusicFinder
{
    public function matches($uri)
    {
        return strpos($uri, 'play.google.com') !== false;
    }

    public function music_info($uri)
    {
        $is_match = preg_match("/play.google.com\/music\/m\/(\w+)/", $uri, $matches);

        if(! $is_match) {
            $is_match = preg_match("play.google.com/music/listen\?.*album/(\w+)", $uri, $matches);
        }

        if($is_match === 1) {
            $info = new MusicInfo;

            $music_info = $this->fetchMusicInfo($matches[1]);
            $info->fill($music_info);

            return $info;
        }
    }

    public function search($info)
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

    protected function fetchMusicInfo($id)
    {
        $uri = sprintf("https://play.google.com/music/preview/%s", $id);

        $response = CurlHelper::factory($uri)->exec();

        $response_body = $response['content'];

        // Initialize crawler
        $crawler = new Crawler($response_body);
        $search_result = $crawler->filter('#main-content-container');
        
        // Get image link
        $el_image = $crawler->filter('.album-art');
        $image = $el_image->attr('src');

        $image = $this->cleanseImageUrl($image);

        // Get track name
        $el_track = $search_result->filter('.title > a');
        $title = $el_track->text();

        // Get artist name
        $el_artist = $search_result->filter('.album-artist > a');
        $artist = $el_artist->text();


        // Get Album/Track from tracklist title
        $el_tracklist_title = $search_result->filter('.tracklist-info-text > .title');
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
