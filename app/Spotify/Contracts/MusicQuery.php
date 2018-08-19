<?php

namespace App\Spotify\Contracts;

interface MusicQuery
{
    public function getType() : string;

    public function getTitle() : string;

    public function getArtist() : string;
}
