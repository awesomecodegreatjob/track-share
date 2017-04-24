<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $guarded = [];
    protected $table = 'music';

    const KEY_LENGTH = 6;

    public static function getByKey($key)
    {
        return static::where('key', $key)->first();
    }

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
     * @param string $url
     * @return static
     */
    public static function createFromUrl($url)
    {
        $origin_music_service = null;
        foreach(static::getMusicServices() as $service) {
            if($service->matches($url)) {
                $origin_music_service = $service;
                break;
            }
        }

        $music_info = $origin_music_service->music_info($url);

        // Get Google Music URL
        $google_music_service = new GoogleMusicFinder;
        $google_music_data = $google_music_service->search($music_info);
        $google_music_url = $google_music_data->link;

        // Get Spotify URL
        $spotify_service = new SpotifyFinder;
        $spotify_data = $spotify_service->search($music_info);
        if($spotify_data)
            $spotify_url = $spotify_data->link;
        else
            $spotify_url = null;

        $music = app(Music::class);

        $music->fill([
            'key' => static::getNewKey(),
            'band' => $music_info->artist,
            'album' => $music_info->album,
            'track' => $music_info->track,
            'image_url' => $music_info->image_link,
            'google_music_url' => $google_music_url,
            'spotify_url' => $spotify_url,
        ]);

        $music->save();

        return $music;
    }

    /**
     * @return \App\Contracts\MusicService[]
     */
    public static function getMusicServices()
    {
        return [
            new GoogleMusicFinder,
            new SpotifyFinder,
        ];
    }
}
