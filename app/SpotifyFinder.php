<?php

namespace App;

use CurlHelper;

class SpotifyFinder
{
    public function matches($uri)
    {
        return strpos($uri, 'open.spotify.com') !== false
            || strpos($uri, 'spotify:album') !== false
            || strpos($uri, 'spotify:track') !== false;
    }

    public function music_id($uri)
    {
        $is_match = preg_match("/\/\/open\.spotify\.com\/(album|track)\/(\w+)/", $uri, $matches);

        if(! $is_match) {
            $is_match = preg_match("/spotify:(album|track):(\w+)/", $uri, $matches);
        }

        if($is_match === 1) {
            return [$matches[1], $matches[2]];
        }
    }

    public function music_info_by_id($type, $id)
    {
        $info = new MusicInfo;

        if($type == 'album') {
            $music_info = $this->fetchAlbumInfo($id);
        } else {
            $music_info = $this->fetchTrackInfo($id);
        }

        $info->fill($music_info);

        return $info;
    }

    public function music_info($uri)
    {
        $is_match = preg_match("/\/\/open\.spotify\.com\/(album|track)\/(\w+)/", $uri, $matches);

        if(! $is_match) {
            $is_match = preg_match("/spotify:(album|track):(\w+)/", $uri, $matches);
        }

        if($is_match === 1) {
            $info = new MusicInfo;

            if('album' == $matches[1]) {
                $album_info = $this->fetchAlbumInfo($matches[2]);
                $info->fill($album_info);
                $info->type = 'album';
            } else {
                $track_info = $this->fetchTrackInfo($matches[2]);
                $info->fill($track_info);
                $info->type = 'track';
            }

            return $info;
        }
    }

    protected function fetchAlbumInfo($album_id)
    {
        $uri = sprintf("https://api.spotify.com/v1/albums/%s", $album_id);

        $response = CurlHelper::factory($uri)->exec();

        // $album_data represents the returned album infomation.
        // @see: https://developer.spotify.com/web-api/get-album/
        $album_data = array_get($response, 'data');

        return [
            'id' => array_get($album_data, 'id'),
            'title' => array_get($album_data, 'name'),
            'type' => 'album',
            'artist' => array_get($album_data, 'artists.0.name'),
            'link' => array_get($album_data, 'external_urls.spotify'),
            'image_link' => array_get($album_data, 'images.0.url'),
        ];
    }

    protected function fetchTrackInfo($track_id)
    {
        $uri = sprintf("https://api.spotify.com/v1/tracks/%s", $track_id);

        $response = CurlHelper::factory($uri)->exec();

        // $track_data represents the returned track infomation.
        // @see: https://developer.spotify.com/web-api/get-track/
        $track_data = array_get($response, 'data');

        return [
            'id' => array_get($track_data, 'id'),
            'title' => array_get($track_data, 'name'),
            'type' => 'track',
            'artist' => array_get($track_data, 'artists.0.name'),
            'link' => array_get($track_data, 'external_urls.spotify'),
            'image_link' => array_get($track_data, 'album.images.0.url'),
        ];
    }

    public function search($info)
    {
        $artist = str_replace(' ', '+', $info->artist);
        $title = str_replace(' ', '+', $info->title);

        $uri = sprintf("https://api.spotify.com/v1/search?q=artist:%s%%20%s:%s&type=%s&limit=1",
            $artist,
            $info->type,
            $title,
            $info->type);

        $response = CurlHelper::factory($uri)->exec();

        // $music_data represents the returned music infomation.
        // @see: https://developer.spotify.com/web-api/search-item/
        $music_data = array_get($response, 'data.'.$info->type.'s.items.0');

        $music_id = array_get($music_data, 'id');

        $music_info = new MusicInfo;
        if($info->type == 'album')
        {
            $music_info->fill($this->fetchAlbumInfo($music_id));
        }
        else
        {
            $music_info->fill($this->fetchTrackInfo($music_id));
        }

        return $music_info;
    }
}