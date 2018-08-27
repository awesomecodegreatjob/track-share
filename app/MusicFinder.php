<?php

namespace App;

use Illuminate\Support\Collection;
use PhpSlang\Option\Option;

class MusicFinder
{
    /** @var MusicServices */
    private $musicServices;

    public function __construct(MusicServices $musicServices)
    {
        $this->musicServices = $musicServices;
    }

    /**
     * @param MusicInfo $info
     * @return Collection<MusicInfo>
     */
    public function collectMatchingInfo(MusicInfo $info) : Collection
    {
        return $this
            ->musicServices
            ->all()
            ->mapWithKeys(function (MusicService $s) use ($info) {
                return $s
                    ->search($info)
                    ->map(function (MusicInfo $result) use ($s) {
                        return [$s->getId() => $result];
                    })
                    ->getOrElse([$s->getId() => MusicInfo::Empty($s->getId())]);
            });
    }

    /**
     * @param string $uri
     * @return Option<MusicService>
     */
    public function findOriginFor(string $uri) : Option
    {
        $origin = $this
            ->musicServices
            ->all()
            ->first(function (MusicService $s) use ($uri) {
                return $s->matches($uri);
            });

        return Option::of($origin);
    }

    /**
     * @param string $uri
     * @return Option<MusicSeed>
     */
    public function getSeedFromUri(string $uri) : Option
    {
        return $this
            ->findOriginFor($uri)
            ->flatMap(function (MusicService $ms) use ($uri) {
                return $ms->musicSeedFromUri($uri);
            });
    }


    // @todo Test this!

    /**
     * @param  MusicSeed  $seed
     * @return  Option<MusicInfo>
     */
    public function getMusicInfoFromSeed(MusicSeed $seed) : Option
    {
        $origin = $this->musicServices->find($seed->getService());

        return $origin->musicInfoFromSeed($seed);
    }
}
