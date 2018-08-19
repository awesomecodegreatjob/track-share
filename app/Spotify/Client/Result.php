<?php

namespace App\Spotify\Client;

use App\MusicInfo;

class Result
{
    /** @var MusicInfo|null */
    private $info;

    public function __construct(?MusicInfo $music)
    {
        $this->info = $music;
    }

    /**
     * @return MusicInfo|null
     */
    public function resolve()
    {
        return $this->info;
    }

    /**
     * @param MusicInfo $music
     * @return Result
     */
    public static function createFoundResult(MusicInfo $music)
    {
        return new static($music);
    }

    /**
     * @return Result
     */
    public static function createEmptyResult()
    {
        return new static(null);
    }
}
