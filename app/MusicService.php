<?php

namespace App;

use App\MusicSeed;
use App\Spotify\Contracts\MusicQuery;
use PhpSlang\Either\Either;
use PhpSlang\Option\Option;

interface MusicService {

    public function getId() : string;

    /**
     * Validates music service URIs
     *
     * @param  string  $uri
     *
     * @return  bool
     */
    function matches($uri);

    /**
     * @param string $uri
     * @return Option<MusicInfo>
     */
    public function musicInfoFromUri(string $uri) : Option;

    /**
     * @param string $uri
     * @return Option<MusicSeed>
     */
    public function musicSeedFromUri(string $uri) : Option;

    public function musicInfoFromSeed(MusicSeed $seed) : Option;

    /**
     * Take in a MusicInfo instance and find the resource ID. Returns null if
     * resource isn't found.
     *
     * @param  MusicInfo  $info
     *
     * @return Option<MusicInfo>
     */
    public function search(MusicInfo $info) : Option;
}
