<?php

namespace App;

use App\Contracts\MusicService;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Music
 * @package App
 *
 * @property string $key
 * @property string $band
 * @property string $album
 * @property string $track
 * @property string $image_url
 * @property string $google_music_url
 * @property string $spotify_url
 */
class Music extends Model
{
    protected $guarded = [];
    protected $table = 'music';

    /** @var int */
    const KEY_LENGTH = 6;

    /**
     * Retrieve Music record by key.
     *
     * @param string $key
     *
     * @return static
     */
    public static function getByKey($key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * Generate unique, random key of size KEY_LENGTH.
     *
     * @return string|null
     */
    public static function getNewKey()
    {
        $hashCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';

        $hash = null;
        while($hash == null || strlen($hash) < Music::KEY_LENGTH)
        {
            $randomIndex = rand(0, strlen($hashCharacters) - 1);
            $hash .= substr($hashCharacters, $randomIndex, 1);
        }

        if(null == Music::getByKey($hash))
        {
            return $hash;
        }
        else
        {
            return static::getNewKey();
        }
    }

    /**
     * Pass in a shared music url from a valid service, return a Music record.
     *
     * @param string $url
     *
     * @return static
     */
    public static function createFromUrl($url)
    {
        $origin_music_service = static::findServiceForUrl($url);

        $music_info = $origin_music_service->music_info($url);

        if($existing_music = static::findExistingMusic($origin_music_service, $music_info))
            return $existing_music;

        $google_music_url = static::getGoogleMusicUrl($music_info);
        $spotify_url = static::getSpotifyUrl($music_info);

        return Music::create([
            'key' => static::getNewKey(),
            'band' => $music_info->artist,
            'album' => $music_info->album,
            'track' => $music_info->track,
            'image_url' => $music_info->image_link,
            'google_music_url' => $google_music_url,
            'spotify_url' => $spotify_url,
        ]);
    }

    /**
     * Retrieve list of all existing music services.
     *
     * @return \App\Contracts\MusicService[]
     */
    public static function getMusicServices()
    {
        return [
            new GoogleMusicFinder,
            new SpotifyFinder,
        ];
    }

    /**
     * Get the origin service for the URL.
     *
     * @param string $url
     *
     * @return \App\Contracts\MusicService|null
     */
    protected static function findServiceForUrl($url)
    {
        $origin_music_service = null;
        foreach(static::getMusicServices() as $service) {
            if($service->matches($url)) {
                $origin_music_service = $service;
                break;
            }
        }

        return $origin_music_service;
    }

    /**
     * Get Google Music Url for given MusicInfo
     *
     * @param \App\MusicInfo $music_info
     *
     * @return string|null
     */
    protected static function getGoogleMusicUrl(MusicInfo $music_info)
    {
        $google_music_service = new GoogleMusicFinder;
        $google_music_data = $google_music_service->search($music_info);
        if($google_music_data)
            return $google_music_data->link;
        else
            return null;
    }

    /**
     * Get Spotify Url for given MusicInfo
     *
     * @param \App\MusicInfo $music_info
     *
     * @return string|null
     */
    protected static function getSpotifyUrl(MusicInfo $music_info)
    {
        $spotify_service = new SpotifyFinder;
        $spotify_data = $spotify_service->search($music_info);
        if($spotify_data)
            return $spotify_data->link;
        else
            return null;
    }

    /**
     * Find existing music key by MusicService and MusicInfo. Returns null
     * if key not found.
     *
     * @param \App\Contracts\MusicService $origin_music_service
     * @param \App\MusicInfo $music_info
     *
     * @return \App\Music|null
     */
    protected static function findExistingMusic(MusicService $origin_music_service, MusicInfo $music_info)
    {
        $existing_music = null;
        $origin_service_class = get_class($origin_music_service);
        switch($origin_service_class)
        {
            case GoogleMusicFinder::class:
                $existing_music = Music::where('google_music_url', $music_info->link)->first();
                break;

            case SpotifyFinder::class:
                $existing_music = Music::where('spotify_url', $music_info->link)->first();
        }

        if($existing_music)
            return $existing_music;
        else
            return null;
    }
}
