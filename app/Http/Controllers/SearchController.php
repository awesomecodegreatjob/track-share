<?php

namespace App\Http\Controllers;

use App\GoogleMusicFinder;
use App\SpotifyFinder;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $url = $request->input('url');

        $gmusic_service = new GoogleMusicFinder;
        $spotify_service = new SpotifyFinder;

        $agent = null;
        $music_info = null;

        if($gmusic_service->matches($url)) {
            $agent = 'Google';
            $music_info = $gmusic_service->music_info($url);
        } else if($spotify_service->matches($url)) {
            $agent = 'Spotify';
            $music_info = $spotify_service->music_info($url);
        }

        return view('action.search.search', [
            'agent' => $agent,
            'info' => $music_info,
        ]);
    }

    public function google($id)
    {

    }

    public function spotify($id)
    {

    }
}
