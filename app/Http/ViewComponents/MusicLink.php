<?php

namespace App\Http\ViewComponents;

use App\GoogleMusicFinder;
use App\MusicInfo;
use App\MusicService;
use App\Spotify\Client\AlbumQuery;
use App\Spotify\Client\TrackQuery;
use App\SpotifyFinder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;

class MusicLink implements Htmlable
{
    /** @var MusicInfo */
    private $info;

    public function __construct($info)
    {
        $this->info = $info;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml() : string
    {
        if ($this->info->getService() === 'spotify') {
            $decoration = [
                'color' => 'green',
                'name' => 'Spotify',
            ];
        } elseif ($this->info->getService() === 'googleMusic') {
            $decoration = [
                'color' => 'orange',
                'name' => 'Google Music',
            ];
        } else {
            $decoration = [
                'color' => 'grey',
                'name' => '',
            ];
        }

        return view('components.music_link', [
            'info' => $this->info,
            'decoration' => $decoration,
        ]);
    }
}
