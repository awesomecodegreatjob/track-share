<?php

namespace App\Http\Controllers;

use App\SpotifyFinder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function html(Request $request, SpotifyFinder $spotify)
    {
        $musicUri = $request->get('q', '');

        $seed = $spotify->musicSeedFromUri($musicUri);

        if ($spotify->matches($musicUri)) {
            return view('pages.music', [
                'requestedMusic' => $spotify->musicInfoFromSeed($seed),
            ]);
        }

        return back();
    }
}
