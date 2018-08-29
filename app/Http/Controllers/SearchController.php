<?php

namespace App\Http\Controllers;

use App\MusicInfo;
use App\MusicLink;
use App\MusicSeed;
use App\MusicFinder;
use App\MusicService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    /** @var MusicFinder */
    private $music;

    public function __construct(MusicFinder $music)
    {
        $this->music = $music;
    }

    public function html(Request $request)
    {
        $musicUri = $request->get('q', '');

        $musicInfo = $this
            ->music
            ->findOriginFor($musicUri)
            ->map(function (MusicService $s) use ($musicUri) {
                return $s->musicInfoFromUri($musicUri);
            })
            ->getOrElse(null);

        /** @var Collection $musicInfoCollection */
        $musicInfoCollection = $musicInfo
            ->map(function (MusicInfo $i) {
                return $this->music->collectMatchingInfo($i);
            })
            ->getOrElse(collect([]));

        $seeds = $musicInfoCollection
            ->map(function (MusicInfo $i) {
                return new MusicSeed($i->getService(), $i->getType(), $i->getId());
            });

        $link = MusicLink::saveSeeds($seeds);

        return redirect()->route('music.link', [ $link->key ]);
    }
}
