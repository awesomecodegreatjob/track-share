<?php

namespace App\Spotify\Client;

use App\Spotify\Contracts\MusicQuery;

class TrackQuery implements MusicQuery
{
    private $queryMap = [];

    public function __construct(string $title, string $artist)
    {
        $this->queryMap['title'] = $title;
        $this->queryMap['artist'] = $artist;
    }

    public function getType(): string
    {
        return 'track';
    }

    public function getTitle(): string
    {
        return $this->queryMap['title'];
    }

    public function getArtist(): string
    {
        return $this->queryMap['artist'];
    }
}
