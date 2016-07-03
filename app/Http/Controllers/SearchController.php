<?php

namespace App\Http\Controllers;

use App\GoogleMusicFinder;
use App\SpotifyFinder;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->gmusic_service = new GoogleMusicFinder;
        $this->spotify_service = new SpotifyFinder;
    }

    public function search(Request $request)
    {
        $url = $request->input('url');

        $agent = null;
        $music_info = null;

        if($this->gmusic_service->matches($url)) {
            $agent = 'Google';
            $google_track_data = $this->gmusic_service->music_id($url);

            return redirect('google/' . $google_track_data);
        } else if($this->spotify_service->matches($url)) {
            $agent = 'Spotify';
            $spotify_track_data = $this->spotify_service->music_id($url);

            return redirect('spotify/' . $spotify_track_data[0] . '/' . $spotify_track_data[1]);
        }
    }

    public function google($id)
    {
        $music_info = $this->gmusic_service->music_info_by_id($id);

        return view('action.search.search', [
            'agent' => 'Google',
            'info' => $music_info,
        ]);
    }

    public function spotify($type, $id)
    {
        $music_info = $this->spotify_service->music_info_by_id($type, $id);

        return view('action.search.search', [
            'agent' => 'Spotify',
            'info' => $music_info,
        ]);
    }
}
