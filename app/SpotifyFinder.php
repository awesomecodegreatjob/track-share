<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use PhpSlang\Option\Option;
use App\Spotify\Contracts\ApiConnection;

class SpotifyFinder implements MusicService
{
    /** @var ApiConnection*/
    private $connection;

    /** @var string */
    private $id;

    public function __construct(string $id, ApiConnection $connection)
    {
        $this->id = $id;
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

    /**
     * @param  string  $uri
     * @return  Option<MusicInfo>
     */
    public function musicInfoFromUri(string $uri) : Option
    {
        return $this
            ->musicId($uri)
            ->flatMap(function (array $match) {
                $seed = new MusicSeed('spotify', $match['type'], $match['id']);
                return $this->musicInfoFromSeed($seed);
            })
            ->map(function (MusicInfo $i) {
                return $i;
            });
    }

    /**
     * Takes a Spotify resource URI and returns the resource type and ID, or
     * false if the URI is not valid.
     *
     * @param  string  $uri
     *
     * @return  Option<string[]>
     */
    public function musicId($uri) : Option
    {
        if (!$this->matches($uri)) {
            return Option::of(null);
        }

        $is_match = preg_match("/\/\/open\.spotify\.com\/(album|track)\/(\w+)/", $uri, $matches);
        if (!$is_match) {
            $is_match = preg_match("/spotify:(album|track):(\w+)/", $uri, $matches);
        }

        if($is_match === 1) {
            return Option::of([
                'type' => $matches[1],
                'id' => $matches[2],
            ]);
        }

        return Option::of(null);
    }

    public function musicInfoFromSeed(MusicSeed $seed) : Option
    {
        $seedKey = sprintf('%s:%s:%s',
            $seed->getService(),
            $seed->getType(),
            $seed->getId()
        );

        if (Cache::has($seedKey)) {
            return Option::of(Cache::get($seedKey));
        }

        return $this->musicInfoById(
            $seed->getType(),
            $seed->getId()
        );
    }

    /**
     * Retrieve music info by resource type and ID. Returns null if resource not found.
     *
     * @param  string  $type
     * @param  string  $id
     *
     * @return Option
     */
    public function musicInfoById($type, $id)
    {
        switch ($type) {
            case 'album':
                $uri = 'v1/albums/'.$id;

                $responseToMusic = function (array $response) {
                    return MusicInfo::fromArray([
                        'service' => $this->getId(),
                        'id' => array_get($response, 'id', ''),
                        'album' => array_get($response, 'name', ''),
                        'track' => '',
                        'type' => 'album',
                        'artist' => array_get($response, 'artists.0.name', ''),
                        'link' => array_get($response, 'external_urls.spotify', ''),
                        'image_link' => array_get($response, 'images.0.url', ''),
                    ]);
                };

                break;

            case 'track':

                $uri = 'v1/tracks/'.$id;

                $responseToMusic = function(array $response) {
                    return MusicInfo::fromArray([
                        'service' => $this->getId(),
                        'id' => array_get($response, 'id', ''),
                        'album' => array_get($response, 'album.name', ''),
                        'track' => array_get($response, 'name', ''),
                        'type' => 'track',
                        'artist' => array_get($response, 'artists.0.name', ''),
                        'link' => array_get($response, 'external_urls.spotify', ''),
                        'image_link' => array_get($response, 'album.images.0.url', ''),
                    ]);
                };

                break;
        }

        $response = $this->connection->request('GET', $uri);

        if(array_get($response, 'error')) {
            return Option::of(null);
        }

        $info = $responseToMusic($response);

        $seedKey = sprintf('%s:%s:%s', $this->getId(), $type, $id);
        Cache::put($seedKey, $info, 180);

        return Option::of($info);
    }

    /**
     * Take in a MusicInfo instance and find the resource ID. Returns null if
     * resource isn't found.
     *
     * @param  MusicInfo  $info
     *
     * @return Option<MusicInfo>
     */
    public function search(MusicInfo $info) : Option
    {
        $title = $info->getType() === 'track' ? $info->getTrack() : $info->getAlbum();

        $search = sprintf('artist:%s %s:%s',
            $info->getArtist(),
            $info->getType(),
            $title
        );

        $response = $this->connection->request('GET', 'v1/search', [
            'q' => $search,
            'type' => $info->getType(),
            'limit' => 1,
        ]);

        $resultType = $info->getType().'s';
        $result = array_get($response, $resultType.'.items.0');

        if (empty($result)) {
            return Option::of(null);
        }

        $artist = ($info->getType() === 'track')
            ? array_get($result, 'album.artists.0.name')
            : array_get($result, 'artists.0.name');

        $albumName = ($info->getType() === 'track')
            ? array_get($result, 'album.name')
            : array_get($result, 'name');

        $coverUrl = ($info->getType() === 'track')
            ? array_get($result, 'album.images.0.url')
            : array_get($result, 'images.0.url');

        $trackName = ($info->getType() === 'track')
            ? array_get($result, 'name')
            : '';

        $music = new MusicInfo(
            $this->getId(),
            array_get($result, 'id'),
            $albumName,
            $trackName,
            array_get($result, 'type'),
            $artist,
            array_get($result, 'external_urls.spotify'),
            $coverUrl
        );

        return Option::of($music);
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $uri
     * @return Option<MusicSeed>
     */
    public function musicSeedFromUri(string $uri): Option
    {
        return $this
            ->musicId($uri)
            ->map(function (array $match) {
                ['id' => $id, 'type' => $type] = $match;

                return Option::of(new MusicSeed($this->getId(), $type, $id));
            })
            ->getOrElse(Option::of(null));
    }
}
