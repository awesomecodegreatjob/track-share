<?php

namespace App;

use App\MusicSeed\MusicSeed;
use App\Spotify\Client\Result;
use App\Spotify\Contracts\MusicQuery;
use App\Spotify\Contracts\ApiConnection;
use App\MusicSeed\Result as MusicSeedResult;

class SpotifyFinder
{
    /** @var ApiConnection*/
    private $connection;

    public function __construct(ApiConnection $connection)
    {
        $this->connection = $connection;
    }

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

    public function musicSeedFromUri(string $uri) : MusicSeedResult
    {
        $isMatch = preg_match("/\/\/open\.spotify\.com\/(album|track)\/(\w+)/", $uri, $matches);
        if (!$isMatch) {
            $isMatch = preg_match("/spotify:(album|track):(\w+)/", $uri, $matches);
        }

        if ($isMatch) {
            return MusicSeed::ok('spotify', $matches[1], $matches[2]);
        } else {
            return MusicSeed::error('Invalid music seed.');
        }
    }

    /**
     * Takes a Spotify resource URI and returns the resource type and ID, or
     * false if the URI is not valid.
     *
     * @param  string  $uri
     *
     * @return  string[]|bool
     */
    public function musicId($uri)
    {
        if (!$this->matches($uri)) {
            return false;
        }

        $is_match = preg_match("/\/\/open\.spotify\.com\/(album|track)\/(\w+)/", $uri, $matches);
        if (!$is_match) {
            $is_match = preg_match("/spotify:(album|track):(\w+)/", $uri, $matches);
        }

        if($is_match === 1) {
            return [$matches[1], $matches[2]];
        }
    }

    public function musicInfoFromSeed(\App\MusicSeed\Result $result)
    {
        return $this->musicInfoById(
            $result->getType(),
            $result->getId()
        );
    }

    /**
     * Retrieve music info by resource type and ID. Returns null if resource not found.
     *
     * @param  string  $type
     * @param  string  $id
     *
     * @return Result
     */
    public function musicInfoById($type, $id)
    {
        switch ($type) {
            case 'album':
                $uri = 'v1/albums/'.$id;

                $responseToMusic = function (array $response) {
                    return MusicInfo::fromArray([
                        'id' => array_get($response, 'id'),
                        'album' => array_get($response, 'name'),
                        'track' => '',
                        'type' => 'album',
                        'artist' => array_get($response, 'artists.0.name'),
                        'link' => array_get($response, 'external_urls.spotify'),
                        'image_link' => array_get($response, 'images.0.url'),
                    ]);
                };

                break;

            case 'track':

                $uri = 'v1/tracks/'.$id;

                $responseToMusic = function(array $response) {
                    return MusicInfo::fromArray([
                        'id' => array_get($response, 'id'),
                        'album' => array_get($response, 'album.name'),
                        'track' => array_get($response, 'name'),
                        'type' => 'track',
                        'artist' => array_get($response, 'artists.0.name'),
                        'link' => array_get($response, 'external_urls.spotify'),
                        'image_link' => array_get($response, 'album.images.0.url'),
                    ]);
                };

                break;
        }

        $response = $this->connection->request('GET', $uri);

        if(array_get($response, 'error')) {
            return Result::createEmptyResult();
        }

        $music = $responseToMusic($response);

        return Result::createFoundResult($music);
    }

    /**
     * Take in a MusicInfo instance and find the resource ID. Returns null if
     * resource isn't found.
     *
     * @param  MusicQuery $query
     *
     * @return Result
     */
    public function search(MusicQuery $query) : Result
    {
        $search = sprintf('artist:%s %s:%s',
            $query->getArtist(),
            $query->getType(),
            $query->getTitle()
        );

        $response = $this->connection->request('GET', 'v1/search', [
            'q' => $search,
            'type' => $query->getType(),
            'limit' => 1,
        ]);

        $result = array_get($response, 'tracks.items.0');

        if (empty($result)) {
            return Result::createEmptyResult();
        }

        $music = new MusicInfo(
            array_get($result, 'id'),
            array_get($result, 'album.name'),
            array_get($result, 'name'),
            'track',
            array_get($result, 'album.artists.0.name'),
            array_get($result, 'href'),
            array_get($result, 'album.images.0.url')
        );

        return Result::createFoundResult($music);
    }

}
