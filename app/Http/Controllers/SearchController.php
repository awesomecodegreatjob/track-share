<?php

namespace App\Http\Controllers;

use App\Music;
use Cache;
use App\GoogleMusicFinder;
use App\SpotifyFinder;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    protected $gmusic_service;
    protected $spotify_service;

    public function __construct()
    {
        $this->gmusic_service = new GoogleMusicFinder;
        $this->spotify_service = new SpotifyFinder;
    }

    /**
     * Take a music share URL and redirect to a shareable music page.
     *
     * HTTP Params:
     *  - {string} url - The music share URL
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function search(Request $request)
    {
        $url = $request->input('url');

        $music = Music::createFromUrl($url);

        return redirect('/m/'.$music->key);
    }

    public function google($id)
    {
        $url = sprintf('https://play.google.com/music/m/%s', $id);

        $music = Music::createFromUrl($url);

        return redirect('/m/'.$music->key);
    }

    public function spotify($type, $id)
    {
        $url = sprintf('spotify:%s:%s', $type, $id);

        $music = Music::createFromUrl($url);

        return redirect('/m/'.$music->key);
    }
}
