<?php

namespace App\Http\Controllers;

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

    public function search(Request $request)
    {
        $url = $request->input('url');

        $agent = null;
        $music_info = null;

        if($this->gmusic_service->matches($url)) {
            $google_track_data = $this->gmusic_service->music_id($url);

            return redirect('google/' . $google_track_data);
        } else if($this->spotify_service->matches($url)) {
            $spotify_track_data = $this->spotify_service->music_id($url);

            return redirect('spotify/' . $spotify_track_data[0] . '/' . $spotify_track_data[1]);
        }
    }

    public function google($id)
    {
        $google_info = $this->gmusic_service->music_info_by_id($id);
        
        $spotify_info = $this->spotify_service->search($google_info);
        
        // temp image fix
        $google_info->image_link = $spotify_info->image_link;

        return view('action.search.search', [
            'agent' => 'Google',
            'info' => $google_info,
            'google_link' => $google_info->link,
            'spotify_link' => $spotify_info->link,
        ]);
    }

    public function spotify($type, $id)
    {
        $spotify_info = $this->spotify_service->music_info_by_id($type, $id);
        
        $google_info = $this->gmusic_service->search($spotify_info);

        return view('action.search.search', [
            'agent' => 'Spotify',
            'info' => $spotify_info,
            'google_link' => $google_info->link,
            'spotify_link' => $spotify_info->link,
        ]);
    }
}
