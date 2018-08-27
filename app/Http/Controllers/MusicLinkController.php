<?php

namespace App\Http\Controllers;

use App\MusicFinder;
use App\MusicInfo;
use App\MusicLink;
use App\MusicSeed;
use Illuminate\View\View;
use Illuminate\Http\Request;

class MusicLinkController extends Controller
{
    /** @var MusicFinder */
    private $finder;

    public function __construct(MusicFinder $finder)
    {
        $this->finder = $finder;
    }

    public function html($id)
    {
        $link = MusicLink::find($id);

        if ($link) {
            return $this->renderMusicInfo($link);
        } else {
            return $this->renderErrorPage('notFound', 'Music link not found.');
        }
    }

    private function renderErrorPage(string $errorType, string $error) : View
    {
        return view('pages.errors.'.$errorType, [
            'error' => $error,
        ]);
    }

    private function renderMusicInfo(MusicLink $link) : View
    {
        $seed = collect($link->seeds)
            ->map(function ($link) {
                return new MusicSeed($link['service'], $link['type'], $link['id']);
            })
            ->first(function (MusicSeed $s) {
                return $s->getId() !== '';
            });

        $info = $this->finder->getMusicInfoFromSeed($seed);

        return $info
            ->map(function (MusicInfo $i) {
                $matches = $this->finder->collectMatchingInfo($i);

                return view('pages.music', [
                    'info' => $i,
                    'matches' => $matches,
                ]);
            })
            ->getOrElse($this->renderErrorPage('notFound', 'There was a problem collecting the music information.'));
    }
}
