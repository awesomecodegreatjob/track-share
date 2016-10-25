<?php

namespace App;

use CurlHelper;

class SpotifyFinder
{
    /**
     * Validates Spotify urls
     *
     * @param  string  $uri
     *
     * @return  bool
     */
    public function matches($uri)
    {
        return strpos($uri, 'open.spotify.com') !== false
            || strpos($uri, 'spotify:album') !== false
            || strpos($uri, 'spotify:track') !== false;
    }

    /**
     * Takes a Spotify resource URI and returns the resource type and ID, or
     * false if the URI is not valid.
     *
     * @param  string  $uri
     *
     * @return  string[ resource type, resource id ]|bool
     */
    public function music_id($uri)
    {
        if( ! $this->matches($uri)) return false;

        $is_match = preg_match("/\/\/open\.spotify\.com\/(album|track)\/(\w+)/", $uri, $matches);

        if(! $is_match) {
            $is_match = preg_match("/spotify:(album|track):(\w+)/", $uri, $matches);
        }

        if($is_match === 1) {
            return [$matches[1], $matches[2]];
        }
    }

    /**
     * Retrieve music info by resource type and ID. Returns null if resource not found.
     *
     * @param  string  $type
     * @param  string  $id
     *
     * @return \App\MusicInfo|null
     */
    public function music_info_by_id($type, $id)
    {
        $info = new MusicInfo;

        if($type == 'album') {
            $music_info = $this->fetchAlbumInfo($id);
        } else {
            $music_info = $this->fetchTrackInfo($id);
        }

        if($music_info === null) return null;

        $info->fill($music_info);

        return $info;
    }

    /**
     * Retrieve music info by URI
     *
     * @param string  $uri
     *
     * @return  \App\MusicInfo
     */
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

    /**
     * Get album info using album ID
     *
     * @param  string  $album_id
     *
     * @return  array|null
     */
    protected function fetchAlbumInfo($album_id)
    {
        $uri = sprintf("https://api.spotify.com/v1/albums/%s", $album_id);

        $response = CurlHelper::factory($uri)->exec();

        // $album_data represents the returned album infomation.
        // @see: https://developer.spotify.com/web-api/get-album/
        $album_data = array_get($response, 'data');

        if(array_get($album_data, 'id') === null) return null;

        return [
            'id' => array_get($album_data, 'id'),
            'title' => array_get($album_data, 'name'),
            'type' => 'album',
            'artist' => array_get($album_data, 'artists.0.name'),
            'link' => array_get($album_data, 'external_urls.spotify'),
            'image_link' => array_get($album_data, 'images.0.url'),
        ];
    }

    /**
     * Get track info using album ID
     *
     * @param  string  $track_id
     *
     * @return  array|null
     */
    protected function fetchTrackInfo($track_id)
    {
        $uri = sprintf("https://api.spotify.com/v1/tracks/%s", $track_id);

        $response = CurlHelper::factory($uri)->exec();

        // $track_data represents the returned track infomation.
        // @see: https://developer.spotify.com/web-api/get-track/
        $track_data = array_get($response, 'data');

        if(array_get($track_data, 'id') === null) return null;

        return [
            'id' => array_get($track_data, 'id'),
            'title' => array_get($track_data, 'name'),
            'type' => 'track',
            'artist' => array_get($track_data, 'artists.0.name'),
            'link' => array_get($track_data, 'external_urls.spotify'),
            'image_link' => array_get($track_data, 'album.images.0.url'),
        ];
    }

    /**
     * Take in a MusicInfo instance and find the resource ID. Returns null if
     * resource isn't found.
     *
     * @param  MusicInfo  $info  Must have artist, title and type properties set
     *
     * @return \App\MusicInfo|null
     */
    public function search(MusicInfo $info)
    {
        if(
            null === $info->title
               || null === $info->artist
               || null === $info->type
          ) {
            throw new \InvalidArgumentException('Required search criteria not provided');
        }

        $artist = str_replace(' ', '+', $info->artist);
        $title = str_replace(' ', '+', $info->title);

        $uri = sprintf("https://api.spotify.com/v1/search?q=artist:%s%%20%s:%s&type=%s&limit=1",
            $artist,
            $info->type,
            $title,
            $info->type);

        $response = CurlHelper::factory($uri)->exec();

        // $music_data represents the returned music information.
        // @see: https://developer.spotify.com/web-api/search-item/
        $music_data = array_get($response, 'data.'.$info->type.'s.items.0');

        $music_id = array_get($music_data, 'id');

        // if a music ID isn't set, return early
        if(null === $music_id) return null;

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
